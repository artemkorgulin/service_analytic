<?php

namespace App\Nova;

use App\Nova\Actions\ForgetAccountsCache;
use App\Nova\Actions\VerifyUser;
use App\Nova\Actions\VerifyPhone;
use App\Nova\Actions\DetachPhone;
use App\Nova\Actions\SendPhoneSMS;
use App\Nova\Actions\NormalizePhone;
use App\Services\JWTImpersonationService;
use App\Services\Billing\OrderService;
use App\Helpers\PhoneHelper;
use App\Rules\PhoneRule;
use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email', 'unverified_phone', 'phone'
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     * @var string[]
     */
    public static $with = [];

    /**
     * @return string
     */
    public static function group(): string
    {
        return __('Users');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $orderings = method_exists($request, 'orderings') ? $request->orderings() : false;
        if (!$orderings) {
            $query->reorder('id', 'asc');
        }

        return parent::indexQuery($request, $query);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public static function label()
    {
        return __('Users');
    }

    public static function singularLabel()
    {
        return __('User');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->email . ' / ' . $this->name;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable()->hideFromDetail(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),
            new Panel(__('Basic Information'), $this->getUserInfoFields()),
            HasMany::make(__('Accounts'), 'accounts', Account::class),
            Tabs::make('Billing', [
                HasMany::make(__('Orders'), 'directOrders', Order::class),
                HasMany::make(__('OldTariffs'), 'tariffActivities', TariffActivity::class),
                HasMany::make(__('PromocodeUser'), 'promocodeUsers', PromocodeUser::class),
            ]),
            Tabs::make('Permissions', [
                MorphToMany::make(__('Roles'), 'roles', \Vyuldashev\NovaPermission\Role::class),
                MorphToMany::make(__('Permissions'), 'permissions', \Vyuldashev\NovaPermission\Permission::class),
            ])
        ];
    }

    public function fieldsForUpdate(Request $request)
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required', 'max:255'),
            Text::make(__('Phone Number'), 'unverified_phone')
                ->help('После изменения - номер будет неверифицированным.')
                ->rules('nullable', new PhoneRule(), 'max:255'),
        ];
    }

    public function getUserInfoFields()
    {
        $is = new JWTImpersonationService(app());

        return
            [
                Text::make(__('Email'), 'email')
                    ->sortable()
                    ->rules('required', 'email', 'max:254')
                    ->creationRules('unique:users,email')
                    ->updateRules('unique:users,email,{{resourceId}}'),

                Text::make(__('Phone Number'), function () {
                    return PhoneHelper::format($this->unverified_phone);
                }),
                Text::make(__('Verified Phone Number'), function () {
                    return PhoneHelper::format($this->phone);
                }),

                Text::make(__('Phone Verification Token'), 'phone_verification_token')->hideFromIndex(),

                DateTime::make(__('Phone Verified At'), 'phone_verified_at')
                    ->format('DD.MM.YYYY')
                    ->pickerDisplayFormat('d.m.Y')
                    ->hideFromIndex(),

                Password::make('Password')
                    ->onlyOnForms()
                    ->creationRules('required', 'string', 'min:8')
                    ->updateRules('nullable', 'string', 'min:8'),

                DateTime::make(__('Email Verified At'), 'email_verified_at')
                    ->format('DD.MM.YYYY')
                    ->pickerDisplayFormat('d.m.Y')
                    ->hideFromIndex(),

                Boolean::make(__('Verified'), function () {
                    return !empty($this->email_verified_at);
                })->hideFromDetail(),

                Text::make('', function ($user) use ($is) {
                    if ($is->isImpersonating() && $is->getSubjectId() == $user->id) {
                        return 'Вы уже вошли под этим пользователем';
                    }

                    return sprintf(
                        '<a class="dim text-primary font-bold" style="text-decoration: none" href="%s">Войти под пользователем</a>',
                        route('admin.impersonation.take', $user)
                    );
                })->asHtml()->hideFromIndex(),

                Text::make(__('Order summary'), function ($user) {
                    $orderService = new OrderService();
                    return view('admin.partials.orders_summary', [
                        'tariff' => $orderService->currentUserTariff($user),
                        'is_free' => $orderService->isFreeTariff($user),
                        'services' => $orderService->activeServices($user)
                    ])->render();
                })->asHtml()->onlyOnDetail(),
            ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Filters\EmailVerificationStatus(),
            new Filters\PhoneVerificationStatus(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new VerifyUser())
                ->canSee(fn () => !$this->email_verified_at)
                ->showOnDetail(),
            new ForgetAccountsCache(),
            new DetachPhone(),
            new VerifyPhone(),
            new SendPhoneSMS()
        ];
    }

    public function authorizedTo(Request $request, $ability): bool
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        if ($ability === 'view' && $this->authorizedToViewAny($request)) {
            return true;
        }

        if ($ability === 'update') {
            return true;
        }

        return false;
    }
}
