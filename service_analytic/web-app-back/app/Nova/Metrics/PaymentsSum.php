<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class PaymentsSum extends Value
{

    use MetricsCacheTrait, MetricsRangesTrait;

    /**
     * Card width
     * @var string
     */
    public $width = '1/4';

    /**
     * Payment type
     * @var string|null
     */
    private ?string $type;

    /**
     * Default range key
     *
     * @var string
     */
    public $selectedRangeKey = 'ALL';


    public function __construct(?string $type = null, $component = null)
    {
        $this->type = $type;
        parent::__construct($component);
    }


    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Order::succeeded()->ofType($this->type);

        return $this->sum($request, $query, 'amount');
    }


    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        $key = 'payments-sum';
        if ($this->type) {
            $key .= '-'.$this->type;
        }

        return $key;
    }


    public function name()
    {
        $name = __('Payments sum');

        if ($this->type) {
            $name .= ' '.match ($this->type) {
                    Order::TYPE_BANK => __('by bank'),
                    Order::TYPE_BANK_CARD => __('by card'),
                };
        }

        return $name;
    }


    /**
     * Count only payments by card
     * @return PaymentsCount
     */
    public function byCard(): self
    {
        $this->type = Order::TYPE_BANK_CARD;

        return $this;
    }


    /**
     * Count only payments by bank
     * @return PaymentsCount
     */
    public function byBank(): self
    {
        $this->type = Order::TYPE_BANK;

        return $this;
    }
}
