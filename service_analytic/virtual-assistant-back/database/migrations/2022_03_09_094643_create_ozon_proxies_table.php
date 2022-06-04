<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOzonProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ozon_proxies', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip')->unique()->index();
            $table->bigInteger('port_http')->nullable();
            $table->bigInteger('port_https')->nullable();
            $table->bigInteger('port_socks4')->nullable();
            $table->bigInteger('port_socks4a')->nullable();
            $table->bigInteger('port_socks5')->nullable();
            $table->bigInteger('port_socks5a')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $proxies = [
            ['ip' => '212.193.162.17', 'port_https' => '46945', 'port_socks5' => '64053', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.162.18', 'port_https' => '46945', 'port_socks5' => '64053', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.167.49', 'port_https' => '59219', 'port_socks5' => '53183', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.162.30', 'port_https' => '46945', 'port_socks5' => '64053', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.40', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.41', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.43', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.44', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.45', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD'],
            ['ip' => '212.193.164.46', 'port_https' => '54054', 'port_socks5' => '61991', 'login' => 'AYhDKGiD', 'password' => 'JdGhYgxD']
        ];

        DB::table('ozon_proxies')->insert($proxies);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ozon_proxies');
    }
}
