<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Service extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Service::class;

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

            Number::make(__('Min order amount'), 'min_order_amount')->hideFromIndex(),
            Number::make(__('Max order amount'), 'max_order_amount')->hideFromIndex(),

            Boolean::make(__('Visible'), 'visible'),
            Boolean::make(__('Countable'), 'countable'),
            Boolean::make(__('Sellable'), 'sellable'),

            HasMany::make(__('ServicePrice'), 'prices', ServicePrice::class),
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
        return __('Services');
    }


    public static function singularLabel(): string
    {
        return __('Service');
    }
}
