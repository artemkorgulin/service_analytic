<?php

namespace Database\Seeders;

use App\Models\OldTariff;
use Illuminate\Database\Seeder;

class TariffPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OldTariff::findOrFail(2)->permissions()->sync([1, 2]);
        OldTariff::findOrFail(1)->permissions()->sync([3]);
    }
}
