<?php

namespace App\Http\Requests;

use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

/**
 * @property int $product_id
 * @property string $site
 * @property string $self_product_link
 * @property string $another_product_link
 * @property string $certificate_link
 */
class EscrowAbuseRequest extends BaseRequest
{
    protected static $RULES = [
        'product_id' => 'integer|required',
        'site' => 'url|required',
        'self_product_link' => 'url|required',
        'another_product_link' => 'url|required',
        'certificate_link' => 'url|required',
    ];

    protected static $ATTRIBUTES = [
        'product_id' => 'номер продукта',
        'site' => 'адресат',
        'self_product_link' => 'ссылка на карточку товара',
        'another_product_link' => 'ссылка на страницу товара нарушителя',
        'certificate_link' => 'ссылка сертификата',
    ];
}
