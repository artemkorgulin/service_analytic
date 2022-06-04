<?php

namespace App\Helpers;

use App\Models\Filter;
use Illuminate\Support\Facades\DB;

class StatisticQueries
{
    /**
     * Подзапрос для поиска средней цены товара за период.
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getAvgPrice(string $joinTable, string $startDate, string $endDate): string
    {
        return 'lateral (
                   SELECT p1.vendor_code, AVG("p1"."sale_price_u"/100) AS sale_price_u, AVG("p1"."sale_price_u"/100 * (100 - "p1"."promo_price_u")/100) AS salePriceEnd
                   FROM prices p1
                   WHERE vendor_code = '.$joinTable.'.vendor_code AND "p1"."created_at" between \''.$startDate.'\' AND  \''.$endDate.'\'
                   GROUP BY p1.vendor_code) AS method_avg_price';
    }

    /** Подзапрос для поиска выручки за период.
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getAvgTakeFunction(string $joinTable, string $startDate, string $endDate): string
    {
        return "lateral (
                    select SUM(COALESCE(p.\"sale_price_u\", 0) / 100 * COALESCE(s.current_sales, 0)) AS take,
                            SUM(COALESCE(s.current_sales, 0)) AS sales,
                            $joinTable.vendor_code AS vendor_code
                    from  (SELECT vendor_code, MAX(current_sales) AS current_sales, \"date\" AS date FROM sales
                                     WHERE vendor_code = $joinTable.vendor_code
                                     AND current_sales > 0
                                     AND \"date\" between '$startDate' AND '$endDate'
                                    GROUP BY vendor_code, \"date\"
                          ) AS s
                        CROSS JOIN
                          lateral (SELECT vendor_code, AVG(\"sale_price_u\") AS \"sale_price_u\" FROM prices
                                     WHERE vendor_code = $joinTable.vendor_code
                                     AND \"date\" = s.\"date\"
                                    GROUP BY vendor_code
                          ) AS p
                ) AS method_avg_take_function
               ";
    }

    /**
     * Подзапрос для поиска последней цены товара за период.
     *
     * @param  string  $joinTable
     * @param  string  $endDate
     * @return string
     */
    public static function getLastPrice(string $joinTable, string $endDate): string
    {
        return sprintf("lateral (
                   SELECT p1.vendor_code, p1.final_price
                   FROM product_info p1
                   WHERE p1.vendor_code = %s.vendor_code AND p1.date <= '%s'
                   ORDER BY p1.date DESC
                   limit 1) AS method_last_price", $joinTable, $endDate);
    }

    /**
     * Подзапрос для поиска количества продаж товара за период.
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getCountProductSales(string $joinTable, string $startDate, string $endDate): string
    {
        return sprintf('lateral (
                   SELECT n.vendor_code,
                          (
                            CASE
                                 WHEN n.sale IS NULL THEN 0
                                 WHEN m.sale IS NULL THEN n.sale - st.sale
                                 ELSE n.sale - m.sale
                            END
                          ) AS sale
                   FROM
                   (SELECT s1.sale, s1.vendor_code AS vendor_code
                   FROM sales s1
                   WHERE s1.vendor_code = %1$s.vendor_code AND "s1"."created_at" between \'%2$s\' AND  \'%3$s\'
                   ORDER BY "s1"."created_at" DESC
                   limit 1) n
                   FULL OUTER JOIN
                   (SELECT s1.sale, s1.vendor_code AS vendor_code
                   FROM sales s1
                   WHERE s1.vendor_code = %1$s.vendor_code AND "s1"."created_at" < \'%2$s\'
                   ORDER BY "s1"."created_at" DESC
                   limit 1) m ON n.vendor_code = m.vendor_code
                   FULL OUTER JOIN
                   (SELECT s1.sale, s1.vendor_code AS vendor_code
                   FROM sales s1
                   WHERE s1.vendor_code = %1$s.vendor_code AND "s1"."created_at" >= \'%2$s\'
                   ORDER BY "s1"."created_at"
                   limit 1) st ON n.vendor_code = st.vendor_code) AS method_count_product_sales', $joinTable,
            $startDate, $endDate);
    }

    /**
     * Кол-во дней с продажами
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getCountDaysWithSales(string $joinTable, string $startDate, string $endDate): string
    {
        return sprintf('lateral (select count(DISTINCT max_count) as days_with_sales, ms.vendor_code from (
                select s.vendor_code, Date(s."created_at"), max(s."sale") as max_count
                from sales s
                where %s."vendor_code" = s."vendor_code"
                    and s."created_at" between \'%s\' and \'%s\'
                    and s."sale" > 0
                group by s.vendor_code, DATE(s."created_at")
                ) ms group by ms.vendor_code
            ) as method_count_days_with_sales',
            $joinTable, $startDate, $endDate
        );
    }

    /**
     * Количество дней, когда товар был в наличии
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getCountDaysInStock(string $joinTable, string $startDate, string $endDate): string
    {
        return sprintf('lateral (select count(*) as days_in_stock, ms.vendor_code from (
                select sj.vendor_code, Date(sj."created_at"), max(sj."qty") as max_count
                from stocks_jsons sj
                where %s."vendor_code" = sj."vendor_code"
                    and sj."created_at" between \'%s\' and \'%s\'
                    and sj."qty" > 0
                group by sj.vendor_code, DATE(sj."created_at")
                ) ms group by ms.vendor_code
            ) as method_count_days_in_stock',
            $joinTable, $startDate, $endDate
        );
    }

    /**
     * @param  string  $joinTable
     * @param  string  $endDate
     * @return string
     */
    public static function getComments(string $joinTable, string $endDate): string
    {
        return sprintf('lateral (select c."comments_count" as comments, c.vendor_code from comments c
            where c.vendor_code = %s.vendor_code and c."created_at" <= \'%s\'
            order by c."created_at"
            limit 1) as method_comments',
            $joinTable, $endDate
        );
    }

    /**
     * @param  string  $joinTable
     * @param  string  $endDate
     * @return string
     */
    public static function getRating(string $joinTable, string $endDate): string
    {
        return sprintf("lateral (select g.grade as rating, g.vendor_code from grades g
            where g.vendor_code = %s.vendor_code and g.date <= '%s'
            order by g.date desc
            limit 1) as method_rating",
            $joinTable, $endDate
        );
    }

    /**
     * Подзапрос для построение дерева категорий без предмета.
     *
     * @param  string  $joinTable
     * @return string
     */
    public static function getCategoryPathWithoutSubject(string $joinTable): string
    {
        return "lateral (SELECT qw.web_id, STRING_AGG(qw.name, '/') || '/' as category, $joinTable.subject_id FROM
                   (SELECT w.name, q.web_id FROM (SELECT ct1.web_id, ct1.lft, ct1.rgt FROM category_trees AS ct1 WHERE ct1.web_id = $joinTable.web_id limit 1) q
                   JOIN
                   category_trees w
                   ON w.lft <= q.lft AND w.rgt >= q.rgt AND w.web_id != 0
                   ORDER BY w.lft asc) qw
                   GROUP BY qw.web_id
                   ) AS method_category_path_without_subject";
    }

    /**
     * Подзапрос для построения дерева категорий включая.
     *
     * @param  string  $joinTable
     * @return string
     */
    public static function getCategoryPath(string $joinTable): string
    {
        return sprintf("lateral (
            select STRING_AGG(ct2.name, '/') as category, ct.web_id
            from public.category_trees ct
            join public.category_trees ct2
                on ct2.lft <= ct.lft and ct2.rgt >= ct.rgt and ct2.web_id != 0 and ct.tree = 1
            where ct.web_id = %s.web_id
            group by ct.web_id
            limit 1
        ) as method_category_path", $joinTable);
    }

    public static function getBreadcrumbsCategory(
        string $joinTable,
        string $aliasName = 'method_breadcrumbs_category'
    ): string {
        $subjectFilters = Filter::SUBJECT;
        return "lateral (
            select ct.web_id,
                case
                    when f.name is not null then ct.path || '/' || f.name
                    else ct.path
                end as category,
                case
                    when f.name is not null then f.name
                    else ct.name
                end as subject
            from category_trees ct
            left join lateral (
                select f.subject_id, f.name
                from filters f
                where f.subject_id = $joinTable.subject_id and f.relation = '$subjectFilters'
                and f.web_id = $joinTable.web_id
                limit 1
            ) f on f.subject_id = $joinTable.subject_id
            where $joinTable.web_id = ct.web_id
            limit 1
        ) as $aliasName";
    }

    /**
     * Подзапрос для поиска последнего наличия товара.
     *
     * @param  string  $joinTable
     * @param  string  $endDate
     * @return string
     */
    public static function getStock(string $joinTable, string $endDate): string
    {
        return sprintf('lateral (
            SELECT pi.vendor_code, pi.stock_count
            FROM product_info pi
            WHERE pi.vendor_code = %s.vendor_code
                AND pi.date = \'%s\'
            limit 1
        ) AS method_stock', $joinTable, $endDate);
    }

    /**
     * Минимальная позиция товара на конец периода с категорией
     *
     * @param  string  $joinTable
     * @param  string  $tableWebId
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getLastMinPositionCategory(
        string $joinTable,
        string $tableWebId,
        string $startDate,
        string $endDate
    ): string {
        return sprintf(
            'lateral (
                select * from (
                    select p.vendor_code, p.subject_id, p.web_id, p.position, p.date,
                    rank() over (partition by p.vendor_code order by p.date desc, p.position, p.web_id)
                    from positions p
                    where p.vendor_code = %1$s.vendor_code and p.web_id in (table %2$s) and p.subject_id = %1$s.subject_id and p.date >= \'%3$s\' and p.date <= \'%4$s\'
                ) p
                where rank = 1
                limit 1
            ) as method_last_position_category',
            $joinTable,
            $tableWebId,
            $startDate,
            $endDate
        );
    }

    /**
     * Средняя позиция по категориям на конец периода
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getPositionAvgEnd(string $joinTable, string $startDate, string $endDate): string
    {
        return
            sprintf('lateral (
                with avg_positions as (
                    select p.web_id, p.subject_id, p.vendor_code, p."date", avg(p."position") as "position"
                    from public.positions p
                    where p.vendor_code = %1$s.vendor_code and p."date" between \'%2$s\' and \'%3$s\'
                    group by p.web_id, p.subject_id, p.vendor_code, p."date"
                )
                select
                    round(avg(avg_positions."position") over (partition by avg_positions."date"))::int as position_end,
                    avg_positions.vendor_code
                from avg_positions
                order by avg_positions."date" desc
                limit 1
            ) as method_position_avg_end', $joinTable, $startDate, $endDate);
    }

    /**
     * Средняя позиция по категориям на начало периода
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getPositionAvgStart(string $joinTable, string $startDate, string $endDate): string
    {
        return
            sprintf('lateral (
                with avg_positions as (
                    select p.web_id, p.subject_id, p.vendor_code, p."date", avg(p."position") as "position"
                    from public.positions p
                    where p.vendor_code = %1$s.vendor_code and p."date" between \'%2$s\' and \'%3$s\'
                    group by p.web_id, p.subject_id, p.vendor_code, p."date"
                )
                select
                    round(avg(avg_positions."position") over (partition by avg_positions."date"))::int as position_start,
                    avg_positions.vendor_code
                from avg_positions
                order by avg_positions."date"
                limit 1
            ) as method_position_avg_start', $joinTable, $startDate, $endDate);
    }

    /**
     * Средняя позиция по поисковому запросу на конец периода
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getSearchRequestAvgEnd(string $joinTable, string $startDate, string $endDate): string
    {
        return
            sprintf('lateral (
                select avg(sr2."position"::int)::int as search_request_end, sr2.vendor_code from search_requests sr2
                    join (select sr.search, max(sr.created_at) as max_date, sr.vendor_code from search_requests sr
                    where sr.vendor_code = %1$s.vendor_code and sr.created_at between \'%2$s\' and \'%3$s\'
                    group by sr.search, sr.vendor_code) mxp
                    on sr2.vendor_code = mxp.vendor_code and sr2.search = mxp.search and sr2.created_at = mxp.max_date
                    where sr2.vendor_code = %1$s.vendor_code and sr2.created_at between \'%2$s\' and \'%3$s\'
                    group by sr2.vendor_code
            ) as method_search_request_avg_end', $joinTable, $startDate, $endDate);
    }

    /**
     * Средняя позиция по поисковому запросу на начало периода
     *
     * @param  string  $joinTable
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    public static function getSearchRequestAvgStart(string $joinTable, string $startDate, string $endDate): string
    {
        return
            sprintf('lateral (
                select avg(sr2."position"::int)::int as search_request_start, sr2.vendor_code from search_requests sr2
                    join (select sr.search, min(sr.created_at) as min_date, sr.vendor_code from search_requests sr
                    where sr.vendor_code = %1$s.vendor_code and sr.created_at between \'%2$s\' and \'%3$s\'
                    group by sr.search, sr.vendor_code) mnp
                    on sr2.vendor_code = mnp.vendor_code and sr2.search = mnp.search and sr2.created_at = mnp.min_date
                    where sr2.vendor_code = %1$s.vendor_code and sr2.created_at between \'%2$s\' and \'%3$s\'
                    group by sr2.vendor_code
            ) as method_search_request_avg_start', $joinTable, $startDate, $endDate);
    }


    /**
     * Получить название предметной категории.
     *
     * @param $joinTable
     * @return string
     */
    public static function getCategorySubjectName($joinTable): string
    {
        return sprintf("lateral (
            SELECT f.subject_id, f.name
                FROM filters AS f
                WHERE f.subject_id = %s.subject_id and f.relation = '%s'
                limit 1
            ) as method_category_subject_name", $joinTable, Filter::SUBJECT
        );
    }

    /**
     * Получить запрос для группировки по предмету.
     *
     * @param  string  $withQuery
     * @param  string  $unionBlockSelect
     * @return string
     */
    public static function getSubjectGroupQuery(string $withQuery, string $unionBlockSelect):string
    {
        return "WITH query AS ($withQuery)
               SELECT * FROM
                   (
                   SELECT * FROM query
                   UNION ALL
                   (SELECT $unionBlockSelect FROM query GROUP BY subject)
                   ) a
               ORDER BY a.subject";
    }

    /**
     * @param $joinTable
     * @param $date
     * @return string
     */
    public static function getImagesQuantity($joinTable, $date): string
    {
        return sprintf("lateral (
            select i.quantity, i.vendor_code, i.date
            from images i
            where i.vendor_code = %s.vendor_code and i.date <= '%s'
            order by i.date desc
            limit 1
        ) as method_images_quantity", $joinTable, $date);
    }

    /**
     * @param $joinTable
     * @param $date
     * @return string
     */
    public static function getCommentsQuantity($joinTable, $date): string
    {
        return sprintf("lateral (
            select c.comments_count, c.vendor_code, c.date
            from public.comments c
            where c.vendor_code = %s.vendor_code and c.date <= '%s'
            order by c.date desc
            limit 1
        ) as method_comments_quantity", $joinTable, $date);
    }

    /**
     * @param $joinTable
     * @param $date
     * @return string
     */
    public static function getSales($joinTable, $date): string
    {
        return sprintf("lateral (
            select s.current_sales, s.vendor_code, s.date
            from public.sales s
            where s.vendor_code = %s.vendor_code and s.date <= '%s'
            order by s.date desc
            limit 1
        ) as method_sales", $joinTable, $date);
    }

    public static function getAggregateProductInfo($joinTable, $startDate, $endDate): string
    {
        return sprintf('lateral (
            with wpi as (
                select sp.vendor_code, sp.date, sp.current_sales, sp.revenue,
                    sp.final_price, sp.sale_price_u, sp.stock_count
                from product_info sp
                where sp.vendor_code = %1$s.vendor_code and sp.date >= \'%2$s\' and sp."date" <= \'%3$s\'
            )
            select wpi.vendor_code,
                sum(wpi.current_sales) as total_sales, sum(wpi.revenue) as revenue,
                count(wpi.current_sales) filter (where wpi.current_sales > 0) as day_with_sales,
                count(wpi.stock_count) filter (where wpi.stock_count > 0) as day_in_stock,
                min(wpi.final_price) as min_price, max(wpi.final_price) as max_price,
                case when count(wpi.current_sales) filter (where wpi.current_sales > 0) = 0
                    then 0
                    else (sum(wpi.revenue) / count(wpi.current_sales) filter (where wpi.current_sales > 0)) * (\'%3$s\'::date - \'%2$s\'::date + 1)
                end as revenue_potential,
                case when count(wpi.current_sales) filter (where wpi.current_sales > 0) = 0
                    then 0
                    else (sum(wpi.revenue) / count(wpi.current_sales) filter (where wpi.current_sales > 0)) * (\'%3$s\'::date - \'%2$s\'::date + 1) - sum(wpi.revenue)
                end as lost_profit,
                case when count(wpi.current_sales) filter (where wpi.current_sales > 0) = 0 or (sum(wpi.revenue) / count(wpi.current_sales) filter (where wpi.current_sales > 0)) * (\'%3$s\'::date - \'%2$s\'::date + 1) - sum(wpi.revenue) = 0
                    then 0
                    else (((sum(wpi.revenue) / count(wpi.current_sales) filter (where wpi.current_sales > 0)) * (\'%3$s\'::date - \'%2$s\'::date + 1) - sum(wpi.revenue)) / ((sum(wpi.revenue) / count(wpi.current_sales) filter (where wpi.current_sales > 0)) * (\'%3$s\'::date - \'%2$s\'::date + 1))) * 100
                end as lost_profit_percent,
                case when sum(wpi.current_sales) = 0
                    then 0
                    else sum(wpi.revenue) / sum(wpi.current_sales)
                end as avg_price,
                sum(wpi.current_sales)::numeric / (\'%3$s\'::date - \'%2$s\'::date + 1) as sales_avg,
                case when count(wpi.stock_count) filter (where wpi.stock_count > 0) = 0
                    then 0
                    else sum(wpi.current_sales)::numeric / count(wpi.stock_count) filter (where wpi.stock_count > 0)
                end as sales_in_stock_avg
            from
                wpi
            group by 1
        ) method_aggregate_product_info', $joinTable, $startDate, $endDate);
    }

    public static function getProductInfo($joinTable, $endDate): string
    {
        return sprintf('lateral (
            select pi.vendor_code, pi.grade, pi.comments_count, pi.images_count, pi.current_sales,
             pi.sale_price_u, pi.final_price, pi.price_u, pi.sale_price, pi.promo_sale
            from product_info pi
            where pi.vendor_code = %s.vendor_code and pi."date" <= \'%s\'
            order by pi.date desc
            limit 1
        ) method_product_info', $joinTable, $endDate);
    }

    public static function getOzProductInfo($joinTable, $startDate, $endDate): string
    {
        return sprintf('lateral (
            select pi.vendor_code, pi.grade, pi.comments_count, pi.images_count,
            pi.price, pi.final_price, pi.credit_product_price
            from product_info pi
            where pi.vendor_code = %s.vendor_code and pi."date" >= \'%s\' and pi."date" <= \'%s\'
            order by pi.date desc
            limit 1
        ) method_oz_product_info', $joinTable, $startDate, $endDate);
    }

    /**
     * @param string $joinTable
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    public static function getAggregateProductInfoForBrands(string $joinTable, string $startDate, string $endDate): string
    {
        return sprintf('lateral (
            select  %1$s.vendor_code as vendor_code,
                    sum(sp.current_sales) as total_sales,
                    sum(sp.revenue) as revenue,
                    AVG(sp.grade) as grade,
                    AVG(sp.price_u) as price_u,
                    AVG(sp.comments_count) as comments_count
                from product_info sp
                where sp.vendor_code = %1$s.vendor_code and sp.date >= \'%2$s\' and sp."date" <= \'%3$s\'
        ) method_aggregate_product_info_for_brands', $joinTable, $startDate, $endDate);
    }

    /**
     * @param string $joinTable
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    public static function getProductCategoriesWithoutDublicate(string $joinTable, string $startDate, string $endDate): string
    {
        return "lateral (
                            select
                                $joinTable.vendor_code as vendor_code,
                                CASE
                                    WHEN d.id is not null THEN d.subject_id
                                    ELSE a.subject_id
                                END as subject_id,
                                CASE
                                    WHEN d.id is not null THEN d.web_id
                                    ELSE a.web_id
                                END as web_id,
                                CASE
                                    WHEN d.id is not null THEN d.position
                                    ELSE 0
                                END as position
                            from
                            (
                                select
                                    *
                                from
                                    public.positions p
                                where
                                    vendor_code = $joinTable.vendor_code
                                    and subject_id is not null
                                    and date >= '$startDate'
                                order by date desc
                                limit 1
                            ) a
                            left join lateral
                            (
                                select
                                    max(date) as date
                                from
                                    public.positions p
                                where
                                    vendor_code = $joinTable.vendor_code
                                    and subject_id = a.subject_id
                                    and date >= '$startDate'
                                    and date <= '$endDate'
                            ) b
                            on 1 = 1
                            left join lateral
                            (
                                select
                                    min(position) as position
                                from
                                    public.positions p
                                where
                                    vendor_code = $joinTable.vendor_code
                                    and subject_id = a.subject_id
                                    and date = b.date
                            ) c
                            on 1 = 1
                            left join lateral
                            (
                                select
                                    *
                                from
                                    public.positions p
                                where
                                    vendor_code = $joinTable.vendor_code
                                    and subject_id = a.subject_id
                                    and date = b.date
                                    and position = c.position
                                order by web_id
                                limit 1
                            ) d
                            on 1 = 1
                ) AS method_product_categories_without_dublicate";
    }
}
