<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\Promocode;

class PromocodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code'=>$this->faker->regexify('demo[a-z0-9]{5,10}')
        ];
    }

    public function discount($amount)
    {
        return $this->state([
            'type' => Promocode::TYPE_DISCOUNT,
            'discount' => $amount
        ]);
    }

    public function bonusWithPayment()
    {
        return $this->state([
            'type' => Promocode::TYPE_BONUS_WITH_PAYMENT,
        ]);
    }

    public function increaseSku($amount)
    {
        return $this->state([
            'type' => Promocode::TYPE_INCREASE_SKU,
            'discount' => $amount
        ]);
    }

    public function moduleAccess()
    {
        return $this->state([
            'type' => Promocode::TYPE_MODULE_ACCESS,
        ]);
    }

    public function balance($amount)
    {
        return $this->state([
            'type' => Promocode::TYPE_BALANCE,
            'discount' => $amount
        ]);
    }

    public function active()
    {
        return $this->state([
            "active" => true,
            "start_at" => Carbon::now()->sub('1 day'),
            "end_at" => Carbon::now()->add('2 years'),
        ]);
    }

    public function stale()
    {
        return $this->state([
            "active" => true,
            "start_at" => Carbon::now()->sub('100 day'),
            "end_at" => Carbon::now()->sub('1 day'),
        ]);
    }

    public function infuture()
    {
        return $this->state([
            "active" => true,
            "start_at" => Carbon::now()->add('1 year'),
            "end_at" => Carbon::now()->add('2 year'),
        ]);
    }

    public function unlimited()
    {
        return $this->state([
            'usage_limit' => -1,
            'multiple_uses' => false,
        ]);
    }

    public function multitime($n=10000)
    {
        return $this->state([
            'usage_limit' => $n,
            'multiple_uses' => false,
        ]);
    }

    public function onetime()
    {
        return $this->state([
            'usage_limit' => 1,
            'multiple_uses' => false,
        ]);
    }

    public function disabled()
    {
        return $this->state([
            "active" => false,
        ]);
    }
}
