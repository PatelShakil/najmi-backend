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
        Schema::create('colors_mst', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("code");
            $table->boolean("enabled")->default(true);
            $table->unsignedBigInteger("created_by");
            $table->foreign("created_by")->references("id")->on("admin_mst")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors_mst');
    }
};
