<?php

namespace App\Models\Clickhouse;

use Bavix\LaravelClickHouse\Database\Eloquent\Model;

class CardProduct extends Model
{
    protected $table = 'card_products';

    /**
     * @param $query
     * @param $category
     * @param $endDate
     * @param $startDate
     * @param  string  $alias
     * @return void
     */
    public function scopeFromCategory($query, $category, $endDate, $startDate, string $alias = 'card_products')
    {
        $query->from(function ($from) use ($category, $endDate, $startDate, $alias) {
            $from->query()
                ->select(
                    'vendor_code',
                    'date',
                    raw('any(revenue) AS revenue'),
                    raw('any(current_sales) AS current_sales'),
                    raw('any(final_price) AS final_price'),
                    raw('any(price_u) AS price_u'),
                    raw('any(basic_sale) AS basic_sale'),
                    raw('any(basic_price_u) AS basic_price_u'),
                    raw('any(promo_sale) AS promo_sale'),
                    raw('any(sale_price) AS sale_price'),
                    raw('any(sale_price_u) AS sale_price_u'),
                    raw('any(category_count) AS category_count'),
                    raw('any(comments_count) AS comments_count'),
                    raw('any(grade) AS grade'),
                    raw('any(images_count) AS images_count'),
                    raw('any(stock_count) AS stock_count'),
                    raw('any(name) AS name'),
                    raw('any(color) AS color'),
                    raw('any(supplier_name) AS supplier_name'),
                    raw('any(brand_name) AS brand_name'),
                    raw('argMin(category, web_id) as category'),
                    raw('argMin(position, web_id) as pos')
                )
                ->from('card_products', 'cp')
                ->whereBetween('cp.date', [$startDate, $endDate])
                ->where("cp.category", 'LIKE', escapeRawQueryString(sprintf("%s%%", $category)))
                ->groupBy('vendor_code', 'date');
        }, $alias);
    }

    public function scopeFromSubcategory($query, $category, $endDate, $startDate, string $alias = 'card_products')
    {
        $query->from(function ($from) use ($category, $endDate, $startDate) {
            $from->query()
                ->select([
                    'cp.vendor_code',
                    raw(sprintf("extract(cp.category, '%s.([^\\/]+)') as category",
                        str_replace('/', '\/', escapeRawQueryString($category,)))),
                    raw('argMax(cp.grade, cp.date) as rating'),
                    raw('argMax(cp.final_price, cp.date) as last_price'),
                    raw('argMax(cp.suppliers_id, cp.date) as supplier'),
                    raw('argMax(cp.brand_id, cp.date) as brand'),
                    raw('max(cp.comments_count) as comments'),
                    raw('sum(cp.revenue) as revenue'),
                    raw('sum(cp.current_sales) as total_sales')
                ])
                ->from('card_products', 'cp')
                ->whereBetween('cp.date', [$startDate, $endDate])
                ->where('cp.category', 'LIKE', escapeRawQueryString(sprintf("%s%%", $category)))
                ->groupBy('cp.vendor_code', 'category');
        }, $alias);
    }

    public function scopeFromBrandCategory($query, $brand, $endDate, $startDate, $alias = 'card_products')
    {
        $query->from(function ($from) use ($brand, $endDate, $startDate) {
            $from->query()
                ->select([
                    'cp.vendor_code',
                    'cp.category',
                    raw('argMax(cp.grade, cp.date) as rating'),
                    raw('argMax(cp.final_price, cp.date) as last_price'),
                    raw('argMax(cp.suppliers_id, cp.date) as supplier'),
                    raw('argMax(cp.brand_id, cp.date) as brand'),
                    raw('max(cp.comments_count) as comments'),
                    raw('sum(cp.revenue) as revenue'),
                    raw('sum(cp.current_sales) as total_sales')
                ])
                ->from('card_products', 'cp')
                ->whereBetween('cp.date', [$startDate, $endDate])
                ->where('cp.category', '!=', '')
                ->where('cp.brand_name', escapeRawQueryString($brand))
                ->groupBy('cp.vendor_code', 'cp.category');
        }, $alias);
    }

    /**
     * @param $query
     * @param $brand
     * @param $endDate
     * @param $startDate
     * @param  string  $alias
     * @return void
     */
    public function scopeFromBrand($query, $brand, $endDate, $startDate, string $alias = 'card_products')
    {
        $query->from(function ($from) use ($brand, $endDate, $startDate, $alias) {
            $from->query()
                ->select(
                    'vendor_code',
                    'date',
                    raw('any(revenue) AS revenue'),
                    raw('any(current_sales) AS current_sales'),
                    raw('any(final_price) AS final_price'),
                    raw('any(price_u) AS price_u'),
                    raw('any(basic_sale) AS basic_sale'),
                    raw('any(basic_price_u) AS basic_price_u'),
                    raw('any(promo_sale) AS promo_sale'),
                    raw('any(sale_price) AS sale_price'),
                    raw('any(sale_price_u) AS sale_price_u'),
                    raw('any(category_count) AS category_count'),
                    raw('any(comments_count) AS comments_count'),
                    raw('any(grade) AS grade'),
                    raw('any(images_count) AS images_count'),
                    raw('any(stock_count) AS stock_count'),
                    raw('any(name) AS name'),
                    raw('any(color) AS color'),
                    raw('any(supplier_name) AS supplier_name'),
                    raw('any(brand_name) AS brand_name'),
                    raw('argMin(category, web_id) as category'),
                    raw('argMin(position, web_id) as pos')
                )
                ->from('card_products', 'cp')
                ->where("cp.brand_name", escapeRawQueryString($brand))
                ->whereBetween('cp.date', [$startDate, $endDate])
                ->groupBy('vendor_code', 'date');
        }, $alias);
    }

    public function scopeFromBrandSellers($query, $brand, $endDate, $startDate, string $alias = 'card_products')
    {
        $query->from(function ($from) use ($brand, $endDate, $startDate) {
            $from->query()
                ->select([
                    'vendor_code',
                    'date',
                    raw('any(revenue) as revenue'),
                    raw('any(current_sales) as current_sales'),
                    raw('any(final_price) as final_price'),
                    raw('any(comments_count) as comments'),
                    raw('any(grade) as rating'),
                    raw('any(supplier_name) as supplier_name')
                ])
                ->from('card_products', 'cp')
                ->where('cp.brand_name', '=', escapeRawQueryString($brand))
                ->where('cp.supplier_name', '!=', '')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('vendor_code', 'date');
        }, $alias);
    }

    public function scopeFromBrandPriceAnalysis($query, $brand, $endDate, $startDate, string $alias = 'card_products')
    {
        $query->from(function ($from) use ($brand, $endDate, $startDate) {
            $from->query()
                ->select([
                    'vendor_code',
                    'date',
                    raw('any(revenue) as revenue'),
                    raw('any(current_sales) as current_sales'),
                    raw('any(final_price) as final_price'),
                    raw('any(suppliers_id) as supplier_id'),
                    raw('any(brand_id) as brand_id')
                ])
                ->from('card_products', 'cp')
                ->where('cp.brand_name', '=', escapeRawQueryString($brand))
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('vendor_code', 'date');
        }, $alias);
    }

    /**
     * @param $query
     * @param  string  $category
     * @param  string  $endDate
     * @param  string  $startDate
     * @param  string  $alias
     * @return void
     */
    public function scopeFromCategoryPriceAnalysis(
        $query,
        string $category,
        string $endDate,
        string $startDate,
        string $alias = 'card_products'
    ) {
        $query->from(function ($from) use ($category, $endDate, $startDate) {
            $from->query()
                ->select([
                    'vendor_code',
                    'date',
                    raw('any(revenue) as revenue'),
                    raw('any(current_sales) as current_sales'),
                    raw('any(final_price) as final_price'),
                    raw('any(suppliers_id) as supplier_id'),
                    raw('any(brand_id) as brand_id')
                ])
                ->from('card_products', 'cp')
                ->where("cp.category", 'LIKE', sprintf("%s%%", $category))
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('vendor_code', 'date');
        }, $alias);
    }

    public function scopePrepareForPaginate($query, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        $query->limit($perPage, $offset);
    }
}
