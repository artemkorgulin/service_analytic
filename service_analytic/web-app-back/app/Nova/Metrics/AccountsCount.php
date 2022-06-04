<?php

namespace App\Nova\Metrics;

use App\Models\Account;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class AccountsCount extends Value
{

    use MetricsCacheTrait, MetricsRangesTrait;

    /**
     * Card width
     * @var string
     */
    public $width = '1/4';


    /**
     * Account platform id
     * @var int|null
     */
    private ?int $platformId;


    /**
     * Default range key
     * @var string
     */
    public $selectedRangeKey = 'ALL';


    public function __construct(?int $platformId = null, $component = null)
    {
        $this->platformId = $platformId;
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
        $query = Account::query();
        if ($this->platformId) {
            $query->ofPlatform($this->platformId);
        }

        return $this->count($request, $query);
    }


    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'account-count-'.$this->platformId;
    }


    public function name(): string
    {
        $name = __('Accounts');

        if ($this->platformId) {
            $name .= ' '.match ($this->platformId) {
                    Account::SELLER_OZON_PLATFORM_ID => __('OZON'),
                    Account::SELLER_WILDBERRIES_PLATFORM_ID => __('WB'),
                };
        }

        return $name;
    }


    /**
     * @return $this
     */
    public function ozon()
    {
        $this->platformId = Account::SELLER_OZON_PLATFORM_ID;

        return $this;
    }


    /**
     * @return $this
     */
    public function wb()
    {
        $this->platformId = Account::SELLER_WILDBERRIES_PLATFORM_ID;

        return $this;
    }
}
