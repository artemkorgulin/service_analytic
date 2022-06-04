<?php

namespace App\Services;

use App\Models\StatisticsEntry;
use DateTime;
use PDO;

class UserStatisticsService
{

    const START_FROM_USER_ID        = 10;
    const DEFAULT_START_FROM_DATE   = '2022-01-11';
    const DATE_FORMAT               = 'Y-m-d';

    const DEFAULT_ROW_VALUES = [
        'registrations_to_date'           => 0,
        'registrations_by_date'           => 0,
        'verified_to_date'                => 0,
        'verified_conversion_by_date'     => 0,
        'verified_by_date'                => 0,
        'verified_conversion_to_date'     => 0,
        'with_account_to_date'            => 0,
        'with_account_conversion_by_date' => 0,
        'with_account_by_date'            => 0,
        'with_account_conversion_to_date' => 0,
        'payment_count_by_date'           => 0,
        'payment_sum_by_date'             => 0,
        'payment_via_bank_count_by_date'  => 0,
        'payment_via_card_count_by_date'  => 0,
        'payment_count_to_date'           => 0,
        'payment_sum_to_date'             => 0,
        'payment_via_card_count_to_date'  => 0,
        'payment_via_bank_count_to_date'  => 0,
        'orders_via_bank_count_by_date'   => 0,
        'orders_via_bank_count_to_date'   => 0,
    ];

    const FIELDS_THAT_DEPEND_ON_PREVIOUS = [
        'orders_via_bank_count_to_date',
        'payment_via_bank_count_to_date',
        'payment_via_card_count_to_date',
        'payment_sum_to_date',
        'payment_count_to_date',
        'with_account_to_date',
        'verified_to_date',
        'registrations_to_date',
    ];

    private string $startFromDate;

    /**
     * @param  string|null  $date date to calculate statistics for (selection of dates depends on this date and compare operator)
     * @param  string  $dateCompareOperator
     */
    public function __construct(private ?string $date = null, private $dateCompareOperator = '=')
    {
        if (!$this->date) {
            $this->date = date(self::DATE_FORMAT);
        }

        switch ($this->dateCompareOperator) {
            case '=':
            case '>':
            case '>=':
                $this->startFromDate = (new DateTime($this->date))->modify('-1 day')->format(self::DATE_FORMAT);
                break;
            case '<':
            case '<=':
            default:
                $this->startFromDate = self::DEFAULT_START_FROM_DATE;
                break;
        }
    }


    /**
     * Get list of all dates between two provided
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  string|null  $step
     * @param  string|null  $format
     *
     * @return string[]
     * @todo move this method to helpers
     */
    private static function getDatesRange(string $startDate, string $endDate, ?string $step = '+1 day', ?string $format = 'Y-m-d'): array
    {
        $dates       = [];
        $currentDate = strtotime($startDate);
        $endDate     = strtotime($endDate);

        if ($currentDate > $endDate) {
            return $dates;
        }

        while ($currentDate <= $endDate) {
            $dates[]     = date($format, $currentDate);
            $currentDate = strtotime($step, $currentDate);
        }

        return $dates;
    }


    /**
     * Calculate user statistics
     *
     * @return array
     */
    public function getUserStatistics(): array
    {
        $startFromUserId = self::START_FROM_USER_ID;
        $startFromDate   = self::DEFAULT_START_FROM_DATE;

        $sql   = <<<sql
with counts as (select distinct u.id, case when ua.account_id is not null then 1 else 0 end as has_account, date(u.created_at) as date, email_verified_at
                from users as u
                         left join user_account ua on u.id = ua.user_id
                         left join accounts a on ua.account_id = a.id and a.deleted_at is not null
                where u.id >= $startFromUserId
                    and date(u.created_at) >= date('$startFromDate')
                    ##{dateCondition}##
                ),
     sums as (select distinct date,
                              count(*) over (order by date)                                                           as registrations_to_date,
                              count(*) over (partition by date)                                                       as registrations_by_date,
                              sum(case when email_verified_at is not null then 1 else 0 end) over (order by date)     as verified_to_date,
                              sum(case when email_verified_at is not null then 1 else 0 end) over (partition by date) as verified_by_date,
                              sum(has_account) over (order by date)                                                   as with_account_to_date,
                              sum(has_account) over (partition by date)                                               as with_account_by_date
              from counts)
select date,
       registrations_by_date,
       registrations_to_date,
       verified_by_date,
       verified_by_date / registrations_by_date     as verified_conversion_by_date,
       verified_to_date,
       verified_to_date / registrations_to_date     as verified_conversion_to_date,
       with_account_by_date,
       with_account_by_date / registrations_by_date as with_account_conversion_by_date,
       with_account_to_date,
       with_account_to_date / registrations_to_date as with_account_conversion_to_date
from sums
order by date desc;
sql;
        $stats = $this->runQuery($this->prepareQuery($sql, 'u.created_at'));

        return $stats;
    }


    /**
     * Calculate payment statistics
     *
     * @return array
     */
    public function getPaymentStatistics(): array
    {
        $startFromDate = self::DEFAULT_START_FROM_DATE;

        $sql = <<<sql
with payments as (select date(created_at) as date, status, type, amount
                  from orders
                  where period > 0 # exclude orders created via administrative panel
                    and date(created_at) >= date('$startFromDate')
                    ##{dateCondition}##
                  )
select distinct date,
                sum(case when status = 'succeeded' then amount else 0 end) over (partition by date)                   as payment_sum_by_date,
                sum(case when status = 'succeeded' then amount else 0 end) over (order by date)                       as payment_sum_to_date,
                sum(case when status = 'succeeded' then 1 else 0 end) over (order by date)                            as payment_count_to_date,
                sum(case when status = 'succeeded' then 1 else 0 end) over (partition by date)                        as payment_count_by_date,
                sum(case when status = 'succeeded' and type = 'bank_card' then 1 else 0 end) over (partition by date) as payment_via_card_count_by_date,
                sum(case when status = 'succeeded' and type = 'bank_card' then 1 else 0 end) over (order by date)     as payment_via_card_count_to_date,
                sum(case when status = 'succeeded' and type = 'bank' then 1 else 0 end) over (partition by date)      as payment_via_bank_count_by_date,
                sum(case when status = 'succeeded' and type = 'bank' then 1 else 0 end) over (order by date)          as payment_via_bank_count_to_date,
                sum(case when type = 'bank' then 1 else 0 end) over (partition by date)                               as orders_via_bank_count_by_date,
                sum(case when type = 'bank' then 1 else 0 end) over (order by date)                                   as orders_via_bank_count_to_date
from payments
order by date desc;
sql;

        $statistics = $this->runQuery($this->prepareQuery($sql));

        return $statistics;
    }


    /**
     * Get all available statistics merged by date
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $userStats    = $this->getUserStatistics();
        $paymentStats = $this->getPaymentStatistics();

        $statistics = $this->mergeStatistics($userStats, $paymentStats);
        $statistics = $this->fillMissingDateRowsWithValues(rows: $statistics, endDate: $this->date, defaultRowValues: self::DEFAULT_ROW_VALUES);
        return $this->replaceEmptyValuesWithPrevious($statistics);
    }


    /**
     * Save statistics to database
     *
     * @return mixed
     */
    public function saveStatistics(): mixed
    {
        $statistics = $this->getStatistics();

        $now = new DateTime;
        foreach ($statistics as &$statisticsItem) {
            $statisticsItem['created_at'] = $now;
        }

        return StatisticsEntry::upsert($statistics, 'date');
    }


    /**
     * Merge given statistics
     * filling empty data and missing date rows with default values
     *
     * @param  array  $userStatistics
     * @param  array  $paymentStatistics
     *
     * @return array
     */
    private function mergeStatistics(array $userStatistics, array $paymentStatistics): array
    {
        $statistics = $this->createDefaultRowsStatistics();

        $userStatistics = array_column($userStatistics, null, 'date');
        $paymentStatistics = array_column($paymentStatistics, null, 'date');

        // Contains the latest payment statistics needed to fill space in statistics
        $lastPaymentStatistics = self::DEFAULT_ROW_VALUES;

        foreach ($statistics as $date => $item) {
            if (!empty($userStatistics[$date])) {
                $statistics[$date] = $userStatistics[$date] + $lastPaymentStatistics;
            }

            if (!empty($paymentStatistics[$date])) {
                $statistics[$date] = $paymentStatistics[$date] + $statistics[$date];
                $lastPaymentStatistics = $paymentStatistics[$date];
            }
        }

        return $statistics;
    }


    /**
     * Run prepared sql query
     * and return results as associative array
     *
     * @param  string  $sql
     *
     * @return array
     */
    private function runQuery(string $sql): array
    {
        return
            \DB::connection()
                ->getReadPdo()
                ->query($sql)
                ->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    /**
     * Prepare sql query
     * by adding
     *
     * @param  string  $sql
     * @param  string  $dateFieldName
     *
     * @return string
     */
    private function prepareQuery(string $sql, string $dateFieldName = 'created_at'): string
    {
        return str_replace(
            '##{dateCondition}##',
            sprintf(
                "and date(%s) %s '%s'",
                $dateFieldName,
                $this->dateCompareOperator,
                $this->date
            ),
            $sql
        );
    }


    /**
     * Add rows with empty values
     * for the dates without statistics
     *
     * @param  array  $rows
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @param  array  $defaultRowValues
     *
     * @return array
     */
    private function fillMissingDateRowsWithValues(
        array $rows,
        ?string $startDate = null,
        ?string $endDate = null,
        array $defaultRowValues
    ): array {
        $dates = array_keys($rows);
        if (!$startDate) {
            $startDate = reset($dates) ?: $endDate;
        }
        if (!$endDate) {
            $endDate = end($dates) ?: $startDate;
        }

        if (!$startDate || !$endDate) {
            return $rows;
        }

        $dates = self::getDatesRange($startDate, $endDate);

        $rows += array_fill_keys($dates, $defaultRowValues);
        foreach ($rows as $date => &$row) {
            if (empty($row['date'])) {
                $row['date'] = $date;
            }
        }

        ksort($rows);

        return $rows;
    }


    /**
     * Replace empty values with previous non-empty
     * because some values shouldn't be smaller than the same values for previous dates
     *
     * @description
     * @param  array  $statistics
     *
     * @return array
     */
    private function replaceEmptyValuesWithPrevious(array $statistics)
    {
        $previousValues = $this->getFirstPreviousRowForTheRange();

        foreach ($statistics as &$statisticsItem) {
            foreach ($previousValues as $field => $value) {
                if (!empty($statisticsItem[$field])) {
                    $previousValues[$field] = $statisticsItem[$field];
                } else {
                    $statisticsItem[$field] = $value;
                }
            }
        }

        return $statistics;
    }


    /**
     * Get array of non-empty values for the row
     * that is previous for the provided date range
     *
     * @return array
     * @throws \Exception
     */
    public function getFirstPreviousRowForTheRange(): array
    {
        if (date($this->startFromDate) < date(self::DEFAULT_START_FROM_DATE)) {
            $this->startFromDate = self::DEFAULT_START_FROM_DATE;
        }

        $previousRow = StatisticsEntry::query()
            ->where('date', $this->startFromDate)
            ->select(self::FIELDS_THAT_DEPEND_ON_PREVIOUS)
            ->get(self::FIELDS_THAT_DEPEND_ON_PREVIOUS)
            ->first()
            ?->makeHidden('month')
            ->toArray();

        if (empty($previousRow)) {
            return array_fill_keys(self::FIELDS_THAT_DEPEND_ON_PREVIOUS, 0);
        }

        return $previousRow;
    }

    /**
     * Creates an array for later adding data.
     * There may be empty days in statistics that need to be filled.
     *
     * @return array
     */
    private function createDefaultRowsStatistics(): array
    {
        $startDate = strtotime(date($this->startFromDate));
        $finishDate = strtotime(date(self::DATE_FORMAT));

        $statistics = [];

        for ($date = $startDate; $date <= $finishDate; $date += 86400) {
            $statistics[date(self::DATE_FORMAT, $date)] = self::DEFAULT_ROW_VALUES;
        }

        return $statistics;
    }
}
