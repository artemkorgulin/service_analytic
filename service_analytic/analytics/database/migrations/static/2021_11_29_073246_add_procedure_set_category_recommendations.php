<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddProcedureSetCategoryRecommendations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = '
            CREATE OR REPLACE PROCEDURE set_category_recommendations(IN d timestamp without time zone)
             LANGUAGE plpgsql
            AS $procedure$
              declare
              pre_row RECORD;
              curs refcursor;
              category_row RECORD;

              begin
                RAISE notice \'дата - %\', d;
                open curs for (select ct.web_id from category_trees ct where ct.tree = 1);
                LOOP
                fetch curs into pre_row;
                if pre_row is null then
                  exit;
                end if;

                select t2.web_id, min(photo.quantity) as photo_min,
                    min(comments."commentsCount") as comments_min,
                    min(price."sale_price_u") as price_min, max(price."sale_price_u") as price_max,
                    avg(price."sale_price_u")::int as price_avg,
                    min(grade.grade) as grade_min,
                    min(sale.current_sales) as sale_min, max(sale.current_sales) as sale_max,
                    avg(sale.current_sales) as sale_avg, now() as created_at, now() as updated_at
                from (
                  select t.*, (max(t."createdAt") over(PARTITION BY t."position")) as "w" from (
                    select pre_row.web_id as web_id, p."position", p.vendor_code, p."createdAt" from positions p
                      where p."createdAt"::date = d::date and p.position < 37
                      and p.subject_id in((
                        select cv.subject_id from category_vendor cv
                        where cv.web_id = pre_row.web_id group by cv.subject_id
                      ))
                      order by p."position", p."createdAt"
                  ) t
                ) t2
                left join lateral (
                    select i.quantity, i.vendor_code, i."createdAt" from images i
                    where i.vendor_code = t2.vendor_code and i."createdAt" <= d
                    order by i."createdAt" desc
                    limit 1
                ) photo on t2.vendor_code = photo.vendor_code
                left join lateral (
                    select c."commentsCount", c.vendor_code, c."createdAt" from public."comments" c
                    where c.vendor_code = t2.vendor_code and c."createdAt" <= d
                    order by c."createdAt" desc
                    limit 1
                ) comments on t2.vendor_code = comments.vendor_code
                left join lateral (
                    select pr."sale_price_u", pr.vendor_code, pr."createdAt" from public.prices pr
                    where pr.vendor_code = t2.vendor_code and pr."createdAt" <= d
                    order by pr."createdAt" desc
                    limit 1
                ) price on t2.vendor_code = price.vendor_code
                left join lateral (
                    select g.grade, g.vendor_code, g."createdAt" from public.grades g
                    where g.vendor_code = t2.vendor_code and g."createdAt" <= d
                    order by g."createdAt" desc
                    limit 1
                ) grade on t2.vendor_code = grade.vendor_code
                left join lateral (
                    select s.current_sales, s.vendor_code, s."createdAt" from public.sales s
                    where s.vendor_code = t2.vendor_code and s."createdAt" <= d
                    order by s."createdAt" desc
                    limit 1
                ) sale on t2.vendor_code = sale.vendor_code
                where t2."createdAt" = t2.w
                group by t2.web_id into category_row;

                if category_row is not null then
                RAISE notice \'web_id = %\', category_row.web_id;
                insert into analytica.static.category_recommendations(web_id, quantity_photos_min,
                    quantity_comments_min, price_min, price_max, price_avg, rating_min,
                    sale_min, sale_max, sale_avg, created_at, updated_at)
                values (category_row.web_id, category_row.photo_min, category_row.comments_min,
                    category_row.price_min, category_row.price_max, category_row.price_avg,
                    category_row.grade_min, category_row.sale_min, category_row.sale_max,
                    category_row.sale_avg, category_row.created_at, category_row.updated_at);
                end if;
                end LOOP;

              end;
            $procedure$;
        ';
        DB::connection('static')->unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP PROCEDURE IF EXISTS set_category_recommendations;';
        DB::connection('static')->unprepared($sql);
    }
}
