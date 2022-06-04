<?php

namespace App\Repositories\V1\Webapp;

use App\Repositories\BaseRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductRepository
 */
class UserRepository
{
    /**
     * @return Builder
     */
    public static function startConditions(): Builder
    {
        return BaseRepository::getWabQuery('users');
    }

    /**
     * @param int $userId
     * @return Model|Builder|object|null
     */
    public function getUserById(int $userId)
    {
        return self::startConditions()
            ->where('id', $userId)
            ->first();
    }
}
