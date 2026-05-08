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
        Schema::create('spend_threshold_offers', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');
            $table->integer('every_nth');
            $table->integer('discount_type')->comment('0 for fixed amount, 1 for percentage');
            $table->decimal('discount_amount', 10, 2);
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spend_threshold_offers');
    }
};
