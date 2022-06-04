<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class UsersCount extends Value
{

    use MetricsCacheTrait, MetricsRangesTrait;

    /**
     * Card width
     * @var string
     */
    public $width = '1/4';

    /**
     * Default range key
     * @var string
     */
    public $selectedRangeKey = 'ALL';

    private Builder $query;

    private string $uriKey = 'users';


    public function __construct($component = null)
    {
        $this->query = User::query();
        $this->name  = __('Users');
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
        return $this->count($request, $this->query);
    }


    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return $this->uriKey;
    }


    /**
     * Count only users
     * that have at least one active account
     *
     * @return $this
     */
    public function withAccounts(): self
    {
        $this->query->has('accounts');

        $this->name   = __('Users With Accounts');
        $this->uriKey .= '-accounts';

        return $this;
    }


    /**
     * Count only users
     * that have attempted first login
     *
     * @return $this
     */
    public function attemptedFirstLogin(): self
    {
        $this->query->where('first_login', true);

        $this->name   .= __('who attempted first login');
        $this->uriKey .= '-attempted-first-login';

        return $this;
    }


    /**
     * Count only users with paid tariff
     *
     * @return $this
     */
    public function withPaidTariff(): self
    {
        $this->query->withPaidTariff();

        $this->name   .= __('with paid tariff');
        $this->uriKey .= '-with-paid-tariff';


        return $this;
    }
}
