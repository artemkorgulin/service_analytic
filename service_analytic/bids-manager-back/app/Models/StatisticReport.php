<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class StatisticReport
 *
 * @package App\Models
 *
 * @param string  $date
 * @param string  $sku
 * @param string  $name
 * @param string  $keyword
 * @param string  $type
 * @param double  $price
 * @param integer $bid
 * @param double  $cost
 * @param integer $shows
 * @param integer $clicks
 * @param double  $ctr
 * @param double  $avg_1000_shows_price
 * @param double  $avg_click_price
 * @param integer $orders
 * @param double  $profit
 * @param integer $popularity
 * @param double  $purchased_shows
 */
class StatisticReport extends Model
{
    public $date;
    public $sku;
    public $name;
    public $keyword;
    public $type;
    public $price;
    public $bid;
    public $cost;
    public $shows;
    public $clicks;
    public $ctr;
    public $avg_1000_shows_price;
    public $avg_click_price;
    public $orders;
    public $profit;
    public $popularity;
    public $purchased_shows;
}
