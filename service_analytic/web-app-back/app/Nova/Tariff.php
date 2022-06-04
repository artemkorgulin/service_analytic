<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Tariff extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Tariff::class;

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
        'id',
    ];

    /**
     * @return string
     */
    public static function group(): string
    {
        return __('Billing2022');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make(__('Title'), 'name')
                ->rules('required')
                ->required(),

            Text::make(__('Alias'), 'alias')
                ->rules('required')
                ->required(),

            Textarea::make(__('Description'), 'description')
                ->rules('required')
                ->required()
                ->alwaysShow(),

            Text::make(__('Description'), 'description')->displayUsing(function ($description) use ($request) {
                return $request->isResourceIndexRequest() ? \Str::limit($description, 40) : $description;
            })->exceptOnForms()->hideFromDetail(),

            Number::make(__('Tariff price'), 'price')->sortable(),

            BelongsToMany::make(__('Services'), 'services', Service::class)->fields(function () {
                return [
                    Number::make(__('Tariff service amount'), 'amount'),
                ];
            }),

            Boolean::make(__('Visible'), 'visible'),

            HasMany::make(__('Orders'), 'orders', Order::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public static function label(): string
    {
        return __('Tariffs');
    }


    public static function singularLabel(): string
    {
        return __('Tariff');
    }
}

