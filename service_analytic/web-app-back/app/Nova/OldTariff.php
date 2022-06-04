<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class OldTariff extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\OldTariff::class;

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
        'name', 'description', 'sku'
    ];


    /**
     * @return string
     */
    public static function group(): string
    {
        return __('Users');
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make(__('Title'), 'name')
                ->rules('required')
                ->required(),

            Textarea::make(__('Description'), 'description')->alwaysShow(),

            Text::make(__('Description'), 'description')->displayUsing(function ($description) use ($request) {
                return $request->isResourceIndexRequest() ? \Str::limit($description, 40) : $description;
            })->exceptOnForms()->hideFromDetail(),


            Number::make(__('SKU'), 'sku')
                ->rules('required')
                ->required(),
        ];
    }


    /**
     * @return string
     */
    public function subtitle(): string
    {
        return __('SKU').' '.$this->sku;
    }


    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function cards(Request $request): array
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
    public function filters(Request $request): array
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
    public function lenses(Request $request): array
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
    public function actions(Request $request): array
    {
        return [];
    }


    public static function label(): string
    {
        return __('OldTariffs');
    }


    public static function singularLabel(): string
    {
        return __('OldTariff');
    }
}
