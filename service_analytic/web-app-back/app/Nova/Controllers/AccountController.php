<?php

namespace App\Nova\Controllers;

use App\Helpers\NovaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SettingsRequest;
use App\Models\Account;
use App\Models\User;
use App\Nova\Account as AccountResource;
use App\Nova\User as UserResource;
use App\Repositories\UserRepository;
use App\Services\AccountServices;
use App\Services\ProxyService;
use App\Services\UserService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Http\Controllers\DeletesFields;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Nova;

class AccountController extends Controller
{

    use DeletesFields;

    const RESOURCE = 'accounts';


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Laravel\Nova\Http\Requests\CreateResourceRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(CreateResourceRequest $request): JsonResponse
    {
        NovaHelper::setResourceParameter($request, self::RESOURCE);

        AccountResource::authorizeToCreate($request);

        AccountResource::validateForCreation($request);

        $settingsRequest = $this->getSettingsRequest($request);

        $user = User::find($request->viaResourceId);
        if (!$user) {
            throw new Exception('Нельзя создать аккаунт без привязки.', 422);
        }

        $settingsRequest->request->add(['platform_id' => $request->platform]);

        $account = Account::saveAccount($settingsRequest, $user);

        [$_, $callbacks] = AccountResource::fill(
            $request, AccountResource::newModel()
        );

        Nova::actionEvent()->forResourceCreate($request->user(), $account)->save();

        collect($callbacks)->each->__invoke();

        $settingsRequest->request->add(['account_id' => $account->id]);
        $this->setTemporaryProducts($user, $settingsRequest);


        return response()->json([
            'id'       => $account->getKey(),
            'resource' => $account->attributesToArray(),
            'redirect' => UserResource::redirectAfterCreate($request, new UserResource($user)),
        ], 201);
    }


    /**
     * @param  DeleteResourceRequest  $request
     *
     * @return JsonResponse|void
     * @see \Laravel\Nova\Http\Controllers\ResourceDestroyController
     */
    public function destroy(DeleteResourceRequest $request)
    {
        NovaHelper::setResourceParameter($request, self::RESOURCE);

        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {

                //user ids should be collected before detaching or deleting model
                $userIds = UserRepository::getUserIdsWhoSelectedAccount($model);

                //detach only if method is being called from related resource
                if ($request->viaRelationship()) {
                    $userId = $request->viaResourceId;

                    UserService::forgetAccountsCacheForUsers($model->users);

                    $model->users()->detach($userId);

                    AccountServices::setFirstAvailableAccountAsSelectedForUsers($userIds);

                    //delete account only if there are no existing connections to it
                    if ($model->users()->exists()) {
                        return;
                    }
                }

                $this->deleteFields($request, $model, false);

                $uses = class_uses_recursive($model);

                if (in_array(Actionable::class, $uses) && !in_array(SoftDeletes::class, $uses)) {
                    $model->actions()->delete();
                }

                $model->companies()->detach();
                $model->delete();

                //clear cache and set first available account only if it wasn't done before
                if (!$request->viaRelationship()) {
                    $users = User::whereIn('id', $userIds)->get();
                    UserService::forgetAccountsCacheForUsers($users);
                    AccountServices::setFirstAvailableAccountAsSelectedForUsers($userIds);
                }

                tap(Nova::actionEvent(), function ($actionEvent) use ($model, $request) {
                    DB::connection($actionEvent->getConnectionName())->table('action_events')->insert(
                        $actionEvent->forResourceDelete($request->user(), collect([$model]))
                            ->map->getAttributes()->all()
                    );
                });
            });
        });

        if ($request->isForSingleResource() && !is_null($redirect = $request->resource()::redirectAfterDelete($request))) {
            return response()->json([
                'redirect' => $redirect,
            ]);
        }
    }


    /**
     * @param  CreateResourceRequest  $request
     *
     * @return SettingsRequest
     */
    private function getSettingsRequest(CreateResourceRequest $request): SettingsRequest
    {
        $input                   = $request->all();
        $input['client_id']      = $input['platform_client_id'];
        $input['client_api_key'] = $input['platform_api_key'];
        $settingsRequest         = new SettingsRequest($input, $input);
        $settingsRequest->setMethod($request->getMethod());

        return $settingsRequest;
    }


    /**
     * @param  Authenticatable  $user
     * @param  SettingsRequest  $request
     *
     * @return Response|JsonResponse
     */
    private function setTemporaryProducts(Authenticatable $user, SettingsRequest $request): Response|JsonResponse
    {
        $version    = 'v2';
        $name       = 'notifications/new-account';
        $configName = ProxyService::getConfigByUri('vp');
        $proxy      = (new ProxyService($configName, $version, $name, $user));

        return $proxy->setRequest($request)->setMethod('post')->handle();
    }
}
