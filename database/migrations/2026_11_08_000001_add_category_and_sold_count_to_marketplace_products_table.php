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
        Schema::table('marketplace_products', function (Blueprint $table) {
            // Recycled product category (see MarketplaceProduct::CATEGORIES)
            $table->string('category', 50)->nullable()->after('description')->index();
            // Total units sold, used for "best selling" sorting
            $table->unsignedInteger('sold_count')->default(0)->after('stock')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['sold_count']);
            $table->dropColumn(['category', 'sold_count']);
        });
    }
};
