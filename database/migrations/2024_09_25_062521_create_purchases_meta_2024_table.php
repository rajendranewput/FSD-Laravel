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
        Schema::create('purchases_meta_2024', function (Blueprint $table) {
            $table->id();
            $table->string('u_key');
            $table->integer('financial_code');
            $table->string('mfrItem_parent_category_code');
            $table->string('mfrItem_category_code');
            $table->string('mfrItem_parent_category_name');
            $table->float('lbs');
            $table->float('gal');
            $table->float('spend');
            $table->integer('cor');
            $table->integer('cfs');
            $table->integer('lcl');
            $table->integer('wri');
            $table->integer('plant');
            $table->date('processing_month_date');
            $table->integer('processing_year');
            $table->timestamps();

            $table->index('financial_code');
            $table->index('cor');
            $table->index('cfs');
            $table->index('lcl');
            $table->index('wri');
            $table->index('plant');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases_meta_2024');
    }
};
