<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyModuleManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable(config('spondonit.module_manager_table'))){
            Schema::table(config('spondonit.module_manager_table'), function (Blueprint $table) {
                $table->uuid('checksum')->nullable()->after('purchase_code');
            });
        }
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable(config('spondonit.module_manager_table'))){
            Schema::table(config('spondonit.module_manager_table'), function (Blueprint $table) {
                //
            });
        }
    }
}
