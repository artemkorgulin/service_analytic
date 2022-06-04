<?php

namespace App\Services\Demo;


use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class TruncateAllDatabasesService
{
    const EXPECT_USER_EMAILS = [
        'toyij39180@tripaco.com'
    ];

    private array $expectAccountIds = [];
    private array $expectCompanyIds = [];
    private array $expectUserIds = [];
    private Connection $dbWab;

    /**
     * Init variables
     */
    public function init()
    {
        $this->dbWab = DB::connection('mysql');
        $this->expectUserIds = $this->dbWab->table('users')
            ->whereIn('email', self::EXPECT_USER_EMAILS)
            ->select(['id'])
            ->pluck('id')
            ->toArray();
        $this->expectAccountIds = $this->dbWab->table('user_account')
            ->whereIn('user_id', $this->expectUserIds)
            ->select(['account_id'])
            ->pluck('account_id')
            ->toArray();
        $this->expectCompanyIds = $this->dbWab->table('user_companies')
            ->whereIn('user_id', $this->expectUserIds)
            ->select(['company_id'])
            ->pluck('company_id')
            ->toArray();
    }

    /**
     * Main method
     */
    public function run()
    {
        $this->init();

        $this->truncateAdm();
        $this->truncateEvent();
        $this->truncateVa();
        $this->truncateWab();
    }

    /**
     * Truncate adsmanagement database
     */
    public function truncateAdm()
    {
        $db = DB::connection('adm');
    }

    public function truncateEvent()
    {
        $db = DB::connection('event');

        $db->table('notification_schemas')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('notification_send_history')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('notification_users')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('failed_jobs')->truncate();
        $db->table('notifications')
            ->leftJoin('notification_users', 'notifications.id', '=', 'notification_users.notification_id')
            ->whereNotIn('user_id', $this->expectUserIds)
            ->delete();
    }

    public function truncateVa()
    {
        $db = DB::connection('va');
        $expectOzonProducts = $db->table('oz_products')->whereIn('user_id', $this->expectUserIds)
            ->pluck('id')
            ->toArray();

        $db->table('cache_locks')->truncate();
        $db->table('collections')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('escrow_abuses')->truncate();
        $db->table('escrow_histories')->truncate();
        $db->table('escrow_hashes')->truncate();
        $db->table('escrow_certificates')->truncate();
        $db->table('failed_jobs')->truncate();
        $db->table('files')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('images')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('job_batches')->truncate();
        $db->table('jobs')->truncate();
        $db->table('jobs_new_account')->truncate();
        $db->table('optimisation_histories')->whereNotIn('product_id', $expectOzonProducts)->delete();
        $db->table('oz_list_goods')->whereNotIn('oz_product_id', $expectOzonProducts)->delete();
        $db->table('oz_list_goods_adds')->whereNotIn('oz_product_id', $expectOzonProducts)->delete();
        $db->table('oz_list_goods_users')->whereNotIn('oz_product_id', $expectOzonProducts)->delete();
        $db->table('oz_product_analytics_data')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('oz_product_change_history_statuses')->truncate();
        $db->table('oz_product_feature_error_history')->truncate();
        $db->table('oz_product_feature_history')->truncate();
        $db->table('oz_product_import_infos')->truncate();
        $db->table('oz_product_positions_history')->truncate();
        $db->table('oz_product_positions_history_graph')->truncate();
        $db->table('oz_product_price_change_history')->truncate();
        $db->table('oz_product_change_history')->delete();
        $db->table('oz_products_features')->whereNotIn('product_id', $expectOzonProducts)->delete();
        $db->table('oz_trigger_change_min_photos')->whereNotIn('product_id', $expectOzonProducts)->delete();
        $db->table('oz_trigger_change_min_reviews')->whereNotIn('product_id', $expectOzonProducts)->delete();
        $db->table('oz_trigger_remove_from_sale')->whereNotIn('product_id', $expectOzonProducts)->delete();
        $db->table('oz_products')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('oz_temporary_products')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('wb_temporary_products')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('wb_products')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $db->table('wb_nomenclatures')->whereNotIn('user_id', $this->expectUserIds)->delete();
    }

    public function truncateWab()
    {
        $this->dbWab->table('abolition_reasons')->truncate();
        $this->dbWab->table('account_info')->whereNotIn('account_id', $this->expectAccountIds)->delete();
        $this->dbWab->table('accounts')->whereNotIn('id', $this->expectAccountIds)->delete();
        $this->dbWab->table('action_events')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('activity_log')->truncate();
        $this->dbWab->table('companies')->whereNotIn('id', $this->expectCompanyIds)->delete();
        $this->dbWab->table('failed_jobs')->truncate();
        $this->dbWab->table('jobs')->truncate();
        $this->dbWab->table('model_has_permissions')->truncate();

        $this->dbWab->table('model_has_roles')->where(function ($query) {
            $query->whereNotIn('model_id', $this->expectAccountIds)->where('model_type', '=', 'App\\Models\\Account');
        })->orWhere(function ($query) {
            $query->whereNotIn('model_id', $this->expectUserIds)->where('model_type', '=', 'App\\Models\\User');
        })->orWhere(function ($query) {
            $query->whereNotIn('model_id', $this->expectCompanyIds)->where('model_type', '=', 'App\\Models\\Company');
        })->delete();

        $this->dbWab->table('orders')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('order_service')->leftJoin('orders', 'order_service.order_id', '=', 'orders.id')
            ->whereNotIn('orders.user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('abolition_reasons')->delete();
        $this->dbWab->table('payment_methods')
            ->leftJoin('orders', 'orders.payment_method_id', '=', 'payment_methods.id')
            ->whereNull('orders.payment_method_id')->delete();
        $this->dbWab->table('payment_recipients')->truncate();
        $this->dbWab->table('promocode_users')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('tariff_activity')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('tariff_permissions')->truncate();
        $this->dbWab->table('user_account')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('user_companies')->whereNotIn('user_id', $this->expectUserIds)->delete();
        $this->dbWab->table('users')->whereNotIn('id', $this->expectUserIds)->delete();
        $this->dbWab->table('user_info')->truncate();
    }
}
