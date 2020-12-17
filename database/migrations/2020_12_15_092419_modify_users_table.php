<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 191)->after('name');
            $table->unsignedBigInteger('role_id')->after('username');
            $table->boolean('is_active')->after('role_id')->default(TRUE);
            $table->string('contact_number', 191)->after('is_active');
            $table->timestamp('mobile_verified_at')->after('contact_number')->nullable();
            $table->uuid('uuid')->after('mobile_verified_at')->nullable();
            $table->string('photo')->after('uuid')->nullable();
            $table->string('avatar')->after('photo')->nullable();
            $table->string('notification_preference')->default('mail')->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
