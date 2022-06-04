<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbDirectory extends Model
{
    use HasFactory;

    public $fillable = ['title', 'slug', 'qty', 'comment'];

    public $hidden = ['pivot', 'created_at', 'updated_at'];

    public $getDirectoryUrl = "https://content-suppliers.wildberries.ru/ns/characteristics-configurator-api/content-configurator/api/v1/directory";

    /**
     * Return get request url
     * @return string
     */
    public function getRequestUrlAttribute(): string
    {
        return join('', [$this->getDirectoryUrl, $this->slug, '?top=', $this->qty]);
    }


    /**
     * Return get request url for
     * @return string
     */
    public function getRequestUrl2Attribute(): string
    {
        return join('', [$this->getDirectoryUrl, $this->slug]);
    }

    /**
     * Return directory items
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(WbDirectoryItem::class);
    }
}
