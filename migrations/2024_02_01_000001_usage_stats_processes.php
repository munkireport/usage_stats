<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsageStatsProcesses extends Migration
{
    private $tableName = 'usage_stats';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->mediumText('processes')->nullable();
            $table->string('cpu_idle')->nullable();
            $table->string('cpu_sys')->nullable();
            $table->string('cpu_user')->nullable();
            $table->string('load_avg')->nullable();
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('processes');
            $table->dropColumn('cpu_idle');
            $table->dropColumn('cpu_sys');
            $table->dropColumn('cpu_user');
            $table->dropColumn('load_avg');
        });
    }
}
