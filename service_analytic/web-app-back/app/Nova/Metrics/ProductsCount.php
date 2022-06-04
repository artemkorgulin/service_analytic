<?php

namespace App\Nova\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class ProductsCount extends Value
{

    use MetricsCacheTrait, MetricsRangesTrait;

    /**
     * Card width
     * @var string
     */
    public $width = '1/4';


    private Builder $query;


    public function __construct($model = null, $name = null, $component = null)
    {
        if ($model) {
            $this->query = $model instanceof Builder ? $model : (new $model)->newQuery();
        }
        $this->name = $name;
        parent::__construct($component);
    }


    /**
     * Default range key
     *
     * @var string
     */
    public $selectedRangeKey = 'ALL';


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
        return 'products-count-'.$this->query->getModel()->getTable();
    }


    public function name()
    {
        return $this->name ?? __('Tracking products');
    }


    public function ozon()
    {
        $this->query = \App\Models\Remote\VA\OzProduct::query();
        $this->name  = __('Tracking products').' '.__('OZON');

        return $this;
    }


    public function wb()
    {
        $this->query = \App\Models\Remote\VA\WbProduct::query();
        $this->name  = __('Tracking products').' '.__('WB');

        return $this;
    }


    public function ozonTemporary()
    {
        $this->query = \App\Models\Remote\VA\OzTemporaryProduct::query();
        $this->name  = __('Temporary products').' '.__('OZON');

        return $this;
    }


    public function wbTemporary()
    {
        $this->query = \App\Models\Remote\VA\WbTemporaryProduct::query();
        $this->name  = __('Temporary products').' '.__('WB');

        return $this;
    }

}
