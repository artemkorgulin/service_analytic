<?php

namespace App\Helpers;


use Illuminate\Database\Eloquent\Builder;

abstract class QueryCondition
{
    const FILTER_MAP = [
        'number' => [
            'equals' => '=',
            'notEqual' => '!=',
            'lessThan' => '<',
            'lessThanOrEqual' => '<=',
            'greaterThan' => '>',
            'greaterThanOrEqual' => '>=',
            'inRange' => '',
        ],
        'text' => [
            'contains' => 'ILIKE',
            'notContains' => 'NOT ILIKE',
            'equals' => '=',
            'notEqual' => '!=',
            'startsWith' => 'ILIKE',
            'endsWith' => 'ILIKE',
        ],
    ];

    /**
     * @param Builder $query
     * @param array|null $filters
     */
    public function prepare($query, array|null $filters)
    {
        if ($filters) {
            foreach ($filters as $fieldName => $filter) {
                $fieldName = static::FIELD_MAP[$fieldName];

                if (isset($filter['operator'])) {
                    $filter['operator'] = strtolower($filter['operator']);
                    $filter['condition1']['filterType'] = $filter['filterType'];
                    $filter['condition2']['filterType'] = $filter['filterType'];
                    $value1 = $this->getValue($filter['condition1'], $fieldName);
                    $value2 = $this->getValue($filter['condition2'], $fieldName);
                    $value = '(' . $value1 . ' ' . $filter['operator'] . ' ' . $value2 . ')';
                } else {
                    $value = $this->getValue($filter, $fieldName);
                }

                $query->whereRaw($value);
            }
        }
    }

    /**
     * @param array $filter
     * @param string $column
     * @return string|void
     */
    private function getValue(array $filter, string $column)
    {
        if ($filter['filterType'] == 'number') {
            if ($filter['type'] == 'inRange') {
                return $column . ' BETWEEN ' . '\'' . $filter['filter'] . '\'' . ' AND ' . '\'' . $filter['filterTo'] . '\'' . '';
            } else {
                return $column . ' ' . static::FILTER_MAP[$filter['filterType']][$filter['type']] . ' ' . '\'' . $filter['filter'] . '\'';
            }
        }

        if ($filter['filterType'] == 'text') {
            $value = '';
            if (in_array($filter['type'], ['contains', 'notContains'])) {
                $value = '%' . $filter['filter'] . '%';
            } elseif (in_array($filter['type'], ['equals', 'notEqual'])) {
                $value = $filter['filter'];
            } elseif ($filter['type'] == 'startsWith') {
                $value = $filter['filter'] . '%';
            } elseif ($filter['type'] == 'endsWith') {
                $value = '%' . $filter['filter'];
            }
            $value = '\'' . $value . '\'';

            return $column . ' ' . static::FILTER_MAP[$filter['filterType']][$filter['type']] . ' ' . $value;
        }
    }

    /**
     * Получить блок select для запроса.
     *
     * @return string
     */
    public function getSelectParams(): string
    {
        $result = '';

        foreach (static::FIELD_MAP as $key => $value) {
            $result .= $value . ' as ' . $key . ', ';
        }

        return substr($result,0,-2);
    }
}
