<?php

use Illuminate\Database\Seeder;

use App\Constants\References\ProductStatusesConstants;
use App\Models\OzProductStatus;
use App\Models\OzProduct;

class ProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $verified = OzProductStatus::firstOrCreate(
            [
                'code' => ProductStatusesConstants::VERIFIED_CODE
            ],
            [
                'name' => ProductStatusesConstants::VERIFIED_NAME,
            ]
        );

        OzProductStatus::firstOrCreate(
            [
                'code' => ProductStatusesConstants::MODERATION_CODE
            ],
            [
                'name' => ProductStatusesConstants::MODERATION_NAME,
            ]
        );

        OzProductStatus::firstOrCreate(
            [
                'code' => ProductStatusesConstants::ERROR_CODE
            ],
            [
                'name' => ProductStatusesConstants::ERROR_NAME,
            ]
        );
    }
}
