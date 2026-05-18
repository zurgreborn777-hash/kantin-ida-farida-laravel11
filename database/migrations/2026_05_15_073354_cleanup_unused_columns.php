<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('points_reward');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('earned_points');
        });

        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
