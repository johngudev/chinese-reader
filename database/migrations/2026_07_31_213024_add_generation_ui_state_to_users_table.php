<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('panel_advanced_open')->nullable()->after('country');
            $table->string('panel_theme', 32)->nullable()->after('panel_advanced_open');
            $table->text('panel_focus_words')->nullable()->after('panel_theme');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['panel_advanced_open', 'panel_theme', 'panel_focus_words']);
        });
    }
};
