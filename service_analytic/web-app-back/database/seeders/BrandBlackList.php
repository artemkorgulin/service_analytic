<?php

namespace Database\Seeders;

use Database\Seeders\dist\black_list\FillInBlackList;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;

class BrandBlackList extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function run()
    {
        (new FillInBlackList)->run();
    }
}
