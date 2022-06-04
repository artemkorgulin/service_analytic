<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Promocode extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Promocode::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'code',
    ];

    public function title() {
        return $this->type_description . " - " . $this->discount . "%";
    }

    /**
     * @return string
     */
    public static function group(): string
    {
        return __('Promocodes');
    }

    public static function label()
    {
        return 'Промокоды';
    }

    public function fieldsForIndex(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Код', 'code')->sortable(),
            Text::make('Лимит использования', function($v){
                if($v->usage_limit == -1){
                    return 'Бесконечно';
                }
                return $v->usage_limit;
            }),
            Text::make('Тип', 'type_description'),
            Number::make('Размер скидки %', 'discount')->sortable(),

            Text::make('Активность', function($promocode){
                return view('admin/partials/promocode_activity', compact('promocode'))
                    ->render();
            })->asHtml(),

            Boolean::make('Включён', 'active')->sortable(),
        ];
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
            Text::make('Код', 'code')
                ->rules('required', 'regex:/^[a-z0-9]+$/i'),
            DateTime::make('Начало действия', 'start_at')
                ->rules('required'),
            DateTime::make('Окончание действия', 'end_at')
                ->rules('required'),
            Number::make('Лимит использования', 'usage_limit')
                ->help('-1 - данный промокод можно использовать бесконечно')
                ->rules('required'),
            Select::make('Тип', 'type')
                ->options(\App\Models\Promocode::typeOptions())
                ->rules('required'),
            Number::make('Размер скидки %', 'discount')
                ->rules('required'),
            Boolean::make('Включён', 'active'),
            HasMany::make(__('PromocodeUser'),'promocodeUsers', PromocodeUser::class),

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
}
