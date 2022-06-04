<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AbstractClasses\QueryFilter;

/**
 * Class ProductFilter
 * @package App\Services
 */
class CampaignsAnalyticsFilter extends QueryFilter
{
    protected $dateTo;
    protected $dateFrom;

    public function provider($value)
    {
       return  $this->builder->where('provider', 'like', '%'.$value.'%');
    }

    public function log_name($value)
    {
        return  $this->builder->where('log_name', 'like', '%'.$value.'%');
    }

    public function subject_type($value)
    {
        return  $this->builder->where('subject_type', $value);
    }

    public function date($value)
    {
        $start = Carbon::parse($value)->startOfDay();
        $end = Carbon::parse($value)->endOfDay();

        return  $this->builder->whereBetween('created_at', [$start, $end])->get();
    }
}
