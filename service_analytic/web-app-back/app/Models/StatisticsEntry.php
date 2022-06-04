<?php

namespace App\Models;

use App\Casts\Percent;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatisticsEntry extends Model implements Jsonable
{
    use HasFactory;

    protected $casts = [
        'date'                            => 'immutable_date:d.m.Y',
        'verified_conversion_by_date'     => Percent::class,
        'verified_conversion_to_date'     => Percent::class,
        'with_account_conversion_by_date' => Percent::class,
        'with_account_conversion_to_date' => Percent::class,
    ];

    protected $appends = ['month'];

    protected $fillable = [
        'registrations_by_date',
        'registrations_to_date',
        'verified_by_date',
        'verified_conversion_by_date',
        'verified_to_date',
        'verified_conversion_to_date',
        'with_account_by_date',
        'with_account_conversion_by_date',
        'with_account_to_date',
        'with_account_conversion_to_date',
        'payment_count_by_date',
        'payment_sum_by_date',
        'payment_via_bank_count_by_date',
        'payment_via_bank_count_to_date',
        'payment_via_card_count_by_date',
        'payment_via_card_count_to_date',
        'orders_via_bank_count_to_date',
        'orders_via_bank_sum_to_date',
    ];


    /**
     * Month accessor
     * @return string
     */
    public function getMonthAttribute(): string
    {
        return $this->date->format('m.Y');
    }
}
