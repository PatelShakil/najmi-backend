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
        Schema::create('stock_mst', function (Blueprint $table) {
            $table->string("barcode_no")->primary();
            $table->string("name");
            $table->unsignedBigInteger("brand_id");
            $table->foreign("brand_id")->references("id")->on("brands_mst")->cascadeOnDelete();
            $table->unsignedBigInteger("category_id");
            $table->foreign("category_id")->references("id")->on("categories_mst")->cascadeOnDelete();
            $table->unsignedBigInteger("color_id")->nullable();
            $table->foreign("color_id")->references("id")->on("colors_mst")->cascadeOnDelete();
            $table->decimal("mrp", 10, 2);
            $table->unsignedBigInteger("created_by");
            $table->foreign("created_by")->references("id")->on("admin_mst")->cascadeOnDelete();
            $table->boolean("is_sold")->default(false);
            $table->unsignedBigInteger("sold_by")->nullable();
            $table->foreign("sold_by")->references("id")->on("worker_mst")->cascadeOnDelete();
            $table->boolean("enabled")->default(true);
            $table->timestamp("sold_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mst');
    }
};
