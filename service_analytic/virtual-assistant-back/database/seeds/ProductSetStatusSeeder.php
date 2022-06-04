<?php

use Illuminate\Database\Seeder;

use App\Constants\References\ProductStatusesConstants;
use App\Models\OzProductStatus;
use App\Models\OzProduct;

class ProductSetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var OzProductStatus $verified */
        $verified = OzProductStatus::query()
            ->where('code', ProductStatusesConstants::VERIFIED_CODE)
            ->first();

        OzProduct::withTrashed()->whereNull('status_id')->update(['status_id' => $verified->id]);
    }
}
