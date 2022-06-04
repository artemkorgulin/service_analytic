<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class CreatePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Permission::query()->insert(['name' => 'user.create', 'guard_name' => 'user.create']);
        Permission::query()->insert(['name' => 'user.edit', 'guard_name' => 'user.edit']);
        Permission::query()->insert(['name' => 'user.show', 'guard_name' => 'user.show']);
        Permission::query()->insert(['name' => 'user.disable', 'guard_name' => 'user.disable']);
        Permission::query()->insert(['name' => 'user.delete', 'guard_name' => 'user.delete']);
        Permission::query()->insert(['name' => 'user.pass', 'guard_name' => 'user.pass']);
        Permission::query()->insert(['name' => 'user.balance', 'guard_name' => 'user.balance']);
        Permission::query()->insert(['name' => 'user.pay', 'guard_name' => 'user.pay']);
        Permission::query()->insert(['name' => 'user.report', 'guard_name' => 'user.report']);
        Permission::query()->insert(['name' => 'guide.show', 'guard_name' => 'guide.show']);
        Permission::query()->insert(['name' => 'good.view', 'guard_name' => 'good.view']);
        Permission::query()->insert(['name' => 'good.edit', 'guard_name' => 'good.edit']);
        Permission::query()->insert(['name' => 'analyzer.add', 'guard_name' => 'analyzer.add']);
        Permission::query()->insert(['name' => 'analyzer.delete', 'guard_name' => 'analyzer.delete']);
        Permission::query()->insert(['name' => 'good.brand.edit', 'guard_name' => 'good.brand.edit']);
        Permission::query()->insert(['name' => 'good.brand.view', 'guard_name' => 'good.brand.view']);
        Permission::query()->insert(['name' => 'good.specification.view', 'guard_name' => 'good.specification.view']);
        Permission::query()->insert(['name' => 'good.specification.edit', 'guard_name' => 'good.specification.edit']);
        Permission::query()->insert(['name' => 'user.create', 'guard_name' => 'user.create']);

        $user = new \stdClass();
        $role = new \stdClass();
        // Выдача прав пользователю
        $user->givePermissionTo('edit articles');

// Присвоение пользователю ролей
        $user->assignRole('writer', 'admin');
// Это можно сделать в виде массива
        $user->assignRole(['writer', 'admin']);

// Выдача прав роли
        $role->givePermissionTo('edit articles');

        return 0;
    }
}
