<?php

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryRecommendation extends Model
{
    use HasFactory;

    protected $connection = 'static';

    public function getPriceMinAttribute($value): float|int
    {
        return $value / 100;
    }

    public function getPriceMaxAttribute($value): float|int
    {
        return $value / 100;
    }

    public function getPriceAvgAttribute($value): float|int
    {
        return $value / 100;
    }

    public function setPriceMinAttribute($value): float|int
    {
        return $value / 100;
    }

    public function setPriceMaxAttribute($value): float|int
    {
        return $value / 100;
    }

    public function setPriceAvgAttribute($value): float|int
    {
        return $value / 100;
    }
}
