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
        Schema::create('prodorders', function (Blueprint $table) {
            $table->id();                                // Primary key

            // Foreign key to prods table
            $table->unsignedBigInteger('prodid');
            $table->foreign('prodid')
                  ->references('id')
                  ->on('prods')
                  ->onDelete('cascade');                // Cascade delete

            $table->integer('quan')->default(1);         // Quantity, default 1
            $table->string('customer');                  // Customer name (varchar)
            $table->timestamps();                        // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodorders');
    }
};
