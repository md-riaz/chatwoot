<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventColumnToActivityLogTable extends Migration
{
    public function up()
    {
        $connection = config('activitylog.database_connection');
        $tableName = config('activitylog.table_name');
        
        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (!Schema::connection($connection)->hasColumn($tableName, 'event')) {
                $table->string('event')->nullable()->after('subject_type');
            }
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropColumn('event');
        });
    }
}
