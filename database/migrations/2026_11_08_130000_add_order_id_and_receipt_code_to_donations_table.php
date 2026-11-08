<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('order_id')->nullable()->index();
            $table->string('receipt_code')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropUnique(['receipt_code']);
            $table->dropColumn(['order_id', 'receipt_code']);
        });
    }
};