<?php

namespace App\Services\Common;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AccountQueryService
{
    private Builder $queryBuilder;
    private string $tableName;

    /**
     * @param string $tableName
     * @param string|null $as
     * @return AccountQueryService
     */
    public function initDataManager(string $tableName, ?string $as = null): AccountQueryService
    {
        $this->queryBuilder = DB::table($tableName, $as);
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param int $userId
     * @param int $accountId
     * @return AccountQueryService
     * @throws \Exception
     */
    public function setUserAccount(
        int $userId,
        int $accountId,
        string $relationTable,
        string $relationField
    ): AccountQueryService {

        if (!$this->queryBuilder) {
            throw new \Exception('You must init database table.');
        }

        $this->queryBuilder
            ->whereExists(function ($query) use ($userId, $accountId, $relationTable, $relationField) {
                $query->from($relationTable)
                    ->where([
                        $relationTable . '.user_id' => $userId,
                        $relationTable . '.account_id' => $accountId
                    ])
                    ->whereNull('deleted_at')
                    ->whereRaw($relationTable . '.' . $relationField . ' = ' . $this->tableName . '.' . $relationField);
            });

        return $this;
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }
}
