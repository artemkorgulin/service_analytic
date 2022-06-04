<?php

namespace App\Repositories\V1\Categories;

use Illuminate\Support\Facades\DB;

class CategorySubjectsRepository
{
    /**
     * @param int $webId
     * @return int[]
     */
    public function getCategories(int $webId): array
    {
        $result = [
            'web_id' => $webId,
        ];

        $result['subjects'] = DB::table('filters')
            ->where('relation_id', 3)
            ->where('web_id', $webId)
            ->pluck('name', 'subject_id');

        return $result;
    }
}
