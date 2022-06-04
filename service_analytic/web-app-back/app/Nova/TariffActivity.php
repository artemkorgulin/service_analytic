<?php

namespace App\Nova;

use App\Models\TariffActivity as TariffActivityModel;
use Illuminate\Http\Request;
use Inspheric\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class TariffActivity extends Resource
{

    private static array $STATUS_TITLES;


    /**
     * Disable global search for the resource
     *
     * @var bool
     */
    public static $globallySearchable = false;


    public function __construct($resource)
    {
        self::$STATUS_TITLES = [
            TariffActivityModel::WAIT    => __('Waiting'),
            TariffActivityModel::ACTVE   => __('Active'),
            TariffActivityModel::INACTVE => __('Inactive')
        ];
        parent::__construct($resource);
    }


    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = TariffActivityModel::class;

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
        'id', 'start_at', 'end_at'
    ];

    public static $with = ['tariff'];

    /**
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * @var int
     */
    public static $perPageViaRelationship = 10;


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
            new Panel('Тариф пользователя '.$this->user?->name, [
                BelongsTo::make(__('User'), 'user', User::class)
                    ->hideWhenUpdating(),

                Text::make(__('OldTariff'), 'tariff.name')
                    ->readonly()
                    ->showOnUpdating(true)
                    ->hideWhenCreating()
                    ->hideFromIndex()
                    ->showOnDetail(false),

                BelongsTo::make(__('OldTariff'), 'tariff', OldTariff::class)
                    ->searchable()
                    ->withSubtitles()
                    ->required()
                    ->rules('required')
                    ->showCreateRelationButton()
                    ->showOnUpdating(false),

                Number::make(__('SKU'), 'tariff.sku')->fillUsing(function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                    [$relation, $attribute] = explode('.', $attribute);
                    $model->getRelationValue($relation)
                        ->setAttribute($attribute, $request->input(str_replace('.', '_', $requestAttribute)))
                        ->save();

                })->showOnIndex()->showOnDetail()->hideWhenCreating()->readonly(),

                Indicator::make(__('Status'), 'status')
                    ->labels(self::$STATUS_TITLES)
                    ->colors([
                        TariffActivityModel::WAIT    => 'yellow',
                        TariffActivityModel::ACTVE   => 'green',
                        TariffActivityModel::INACTVE => 'inactive'
                    ])->sortable()->showOnDetail(),

                Select::make(__('Status'), 'status')
                    ->options(self::$STATUS_TITLES)
                    ->rules('required')
                    ->required()
                    ->onlyOnForms(),

                Date::make(__('Start Date'), 'start_at')
                    ->format('DD.MM.YYYY')
                    ->pickerDisplayFormat('d.m.Y')
                    ->rules('required')
                    ->required(),

                Date::make(__('End Date'), 'end_at')
                    ->format('DD.MM.YYYY')
                    ->pickerDisplayFormat('d.m.Y')
                    ->rules('required')
                    ->required()
            ])
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


    public static function label(): string
    {
        return __('Tariff Activities');
    }


    public static function singularLabel(): string
    {
        return __('Tariff Activity');
    }
}
