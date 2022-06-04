<?php

namespace App\Helpers\QueryConditions;

abstract class QueryConditionForClickhouse
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
     * @param $query
     * @param  array|null  $filters
     * @return mixed|void
     */
    public function prepare($query, array|null $filters)
    {
        if (!$filters) {
            return;
        }

        foreach ($filters as $fieldName => $filter) {

            if (in_array($fieldName, static::FIELD_MAP)) {
                if (isset($filter['operator'])) {
                    $filter['operator'] = strtolower($filter['operator']);
                    $filter['condition1']['filterType'] = $filter['filterType'];
                    $filter['condition2']['filterType'] = $filter['filterType'];
                    $value1 = $this->getValue($filter['condition1'], $fieldName);
                    $value2 = $this->getValue($filter['condition2'], $fieldName);
                    $value = '('.$value1.' '.$filter['operator'].' '.$value2.')';
                } else {
                    $value = $this->getValue($filter, $fieldName);
                }

                $query->havingRaw($value);
            }
        }

        return $query;
    }

    /**
     * @param  array  $filter
     * @param  string  $column
     * @return string|void
     */
    private function getValue(array $filter, string $column)
    {
        if ($filter['filterType'] === 'number') {
            if ($filter['type'] === 'inRange') {
                return sprintf("%s BETWEEN '%s' AND '%s'", $column, $filter['filter'], $filter['filterTo']);
            } else {
                return sprintf(
                    "%s %s '%s'",
                    $column, static::FILTER_MAP[$filter['filterType']][$filter['type']], $filter['filter']
                );
            }
        }

        if ($filter['filterType'] === 'text') {
            $value = '';
            if (in_array($filter['type'], ['contains', 'notContains'])) {
                $value = sprintf("%%%s%%", $filter['filter']);
            } elseif (in_array($filter['type'], ['equals', 'notEqual'])) {
                $value = $filter['filter'];
            } elseif ($filter['type'] === 'startsWith') {
                $value = sprintf("%s%%", $filter['filter']);
            } elseif ($filter['type'] === 'endsWith') {
                $value = sprintf("%%%s", $filter['filter']);
            }
            $value = sprintf("'%s'", $value);

            return sprintf("%s %s %s", $column, static::FILTER_MAP[$filter['filterType']][$filter['type']], $value);
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
            $result .= sprintf("%s as %s, ", $value, $key);
        }

        return substr($result, 0, -2);
    }
}
