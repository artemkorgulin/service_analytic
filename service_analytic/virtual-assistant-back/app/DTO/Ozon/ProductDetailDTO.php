<?php

namespace App\DTO\Ozon;

use App\Models\OzProduct;
use App\Services\Escrow\EscrowService;
use Illuminate\Database\Eloquent\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class ProductDetailDTO extends DataTransferObject
{
    public OzProduct $product;
    public Collection $analyticsData;
    public array $listGoodsAdds;
    public EscrowService $escrowService;
}
