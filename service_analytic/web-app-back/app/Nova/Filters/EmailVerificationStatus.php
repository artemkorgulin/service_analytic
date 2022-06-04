<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class EmailVerificationStatus extends Filter
{
    public $name;
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public function __construct()
    {
        $this->name = __('Email Verified');
    }


    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string $isVerified
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $isVerified)
    {
        if ($isVerified === 'verified') {
            return $query->whereNotNull('email_verified_at');
        } elseif ($isVerified === 'unverified') {
            return $query->whereNull('email_verified_at');
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('Verified') => 'verified',
            __('Unverified') => 'unverified',
        ];
    }
}
