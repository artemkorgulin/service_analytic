<?php

namespace App\Nova;

use App\Models\Account as AccountModel;
use Benjacho\BelongsToManyField\BelongsToManyField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Account extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AccountModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'description'
    ];

    /**
     * @return string
     */
    public static function group(): string
    {
        return __('Users');
    }

    public static $with = ['users'];

    /**
     * @var bool
     */
    public static $displayInNavigation = true;


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->hideFromDetail(),

            BelongsTo::make(__('Platform ID'), 'platform', Platform::class)
                ->required()
                ->readonly([$this, 'isUpdateRequest']),

            Boolean::make(__('Selected'), 'pivot.is_selected')
                ->onlyOnIndex()
                ->hideFromIndex(function ($request) {
                    return !$request->viaResource();
                }),

            BelongsToManyField::make(__('Users'), 'users', User::class)->hideFromIndex(),
            BelongsToMany::make(__('Users'), 'users', User::class)->hideFromIndex(),
            Text::make(__('Title'), 'title')->sortable()->rules(['nullable', 'string']),
            Text::make(__('Description'), 'description'),

            Text::make(__('Client ID'), 'platform_client_id')
                ->readonly([$this, 'isUpdateRequest'])
                ->rules([
                    'string',
                    Rule::unique('accounts')->where(function($query) use ($request) {
                        return $query->where([
                            ['platform_api_key', '=', $request->platform_api_key],
                            ['platform_id', '=', $request->platform_id],
                            ['id', '!=', $this->model()->id]
                        ]);
                    })->whereNull('deleted_at'),
                ]),

            Text::make(__('API Key'), 'platform_api_key')
                ->hideFromIndex()
                ->rules(['string', Rule::unique('accounts')->where(function($query) use ($request) {
                    return $query->where([
                        ['platform_client_id', '=', $request->platform_client_id],
                        ['platform_id', '=', $request->platform_id],
                        ['id', '!=', $this->model()->id]
                    ])->whereNull('deleted_at');
                })]),
            MorphToMany::make(__('Roles'), 'roles', \Vyuldashev\NovaPermission\Role::class),
            MorphToMany::make(__('Permissions'), 'permissions', \Vyuldashev\NovaPermission\Permission::class),
        ];
    }


    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }


    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public function authorizedToDelete(Request $request)
    {
        return true;
    }


    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }


    public static function label()
    {
        return __('Accounts');
    }


    public static function singularLabel()
    {
        return __('Account');
    }


    /**
     * @param  NovaRequest  $request
     *
     * @return bool
     */
    public function isUpdateRequest(NovaRequest $request): bool
    {
        return $request->isUpdateOrUpdateAttachedRequest();
    }
}
