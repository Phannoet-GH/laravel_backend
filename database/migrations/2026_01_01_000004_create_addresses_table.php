<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('user_id', 60)->nullable()->index();
            $table->string('recipient_name', 150);
            $table->string('street', 255);
            $table->string('city', 100);
            $table->string('state', 50);
            $table->string('zip_code', 30);
            $table->string('country', 100)->default('United States');
            $table->string('phone', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
