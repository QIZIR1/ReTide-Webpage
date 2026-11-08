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
        Schema::table('payments', function (Blueprint $table) {
            // Snapshot of purchased marketplace items: [{product_id, name, price, quantity}]
            $table->json('items')->nullable()->after('payload');
            // Set once stock & sold_count have been applied, so it only happens once
            $table->timestamp('fulfilled_at')->nullable()->after('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['items', 'fulfilled_at']);
        });
    }
};
