<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OzonProxy extends Model
{
    protected $fillable = [
        'ip',
        'login',
        'port_http',
        'port_https',
        'port_socks4',
        'port_socks4a',
        'port_socks5',
        'password',
    ];

    use HasFactory, SoftDeletes;
}
