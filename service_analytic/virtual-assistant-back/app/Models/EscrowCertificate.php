<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowCertificate extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'lang', 'link', 'full_name', 'copyright_holder', 'email', 'nmid'];
}
