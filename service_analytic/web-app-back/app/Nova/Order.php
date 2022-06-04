<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Hidden;

use App\Services\CalculateService;
use App\Nova\Actions\MakeOrderPaid;
use App\Models\Order as Model;

class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Order::class;

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
    ];

    public static $displayInNavigation = true;


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
            Select::make(__('Order period'), 'period')
                ->options((new CalculateService())->possiblePeriods)
                ->rules('required')
                ->displayUsingLabels(),

            Text::make(__('Order amount'), function ($model) {
                return $model->amount .
                    ' '.
                    ((strtolower($model->currency) == 'rub') ? "₽" : $model->currency);
            })->exceptOnForms(),

            BelongsTo::make(__('Order tariff'), 'Tariff', Tariff::class)->nullable(),

            BelongsTo::make(__('User'), 'user', User::class),

            Select::make(__('Order type'), 'type')
                ->options([
                    Model::TYPE_BANK => __('order.type.'.Model::TYPE_BANK),
                    Model::TYPE_BANK_CARD => __('order.type.'.Model::TYPE_BANK_CARD),
                ])
                ->rules('required')
                ->displayUsingLabels(),

            Select::make(__('Order status'), 'status')
                ->options([
                    Model::SUCCEEDED => __('order.status.'.Model::SUCCEEDED),
                    Model::PENDING => __('order.status.'.Model::PENDING),
                    Model::CANCELED => __('order.status.'.Model::CANCELED)
                ])
                ->rules('required')
                ->displayUsingLabels(),

            DateTime::make(__('Order start date'), 'start_at'),
            DateTime::make(__('Order end date'), 'end_at'),
            DateTime::make(__('Created at'), 'created_at')->exceptOnForms(),

            BelongsTo::make(__('PromocodeUser'), 'promocodeUser', PromocodeUser::class)->onlyOnDetail(),

            BelongsTo::make(__('Order company'), 'company', Company::class)
                ->nullable()
                ->hideFromIndex(),

            Hidden::make('status')->default('pending')->showOnCreating(),

            BelongsToMany::make(__('Order services'), 'services', Service::class)
              ->fields(function ($request, $relatedModel) {
                  return [
                    Number::make(__('Ordered amount'), 'ordered_amount')->default(0)->rules('required'),
                    Number::make(__('Total price'), 'total_price')->default(0)->rules('required'),
                  ];
              }),
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
        return [
            new MakeOrderPaid(),
        ];
    }
    public static function authorizedToCreate(Request $request): bool
    {
        return true;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }


    public static function label(): string
    {
        return __('Orders');
    }

    public static function singularLabel(): string
    {
        return __('Order');
    }
}
