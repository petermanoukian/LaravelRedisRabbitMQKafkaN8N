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
        Schema::create('prods', function (Blueprint $table) {
            $table->id();

            // Foreign key to cats table
            $table->unsignedBigInteger('catid');
            $table->foreign('catid')->references('id')->on('cats')->onDelete('cascade');

            // Required fields
            $table->string('name');
            $table->string('des');

            // Optional fields
            $table->text('dess')->nullable();
            $table->string('filer')->nullable();
            $table->string('img')->nullable();
            $table->string('img2')->nullable();

            // Unique constraint on (catid, name)
            $table->unique(['catid', 'name']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prods');
    }
};

