<?php

namespace Database\Seeders;

use Database\Seeders\dist\black_list\OzonPermission;
use Database\Seeders\dist\black_list\WbPermission;
use Illuminate\Database\Seeder;

class BlackListBindRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new \Database\Seeders\dist\black_list\BindRole(new OzonPermission()))->run();
        (new \Database\Seeders\dist\black_list\BindRole(new WbPermission()))->run();
    }
}
