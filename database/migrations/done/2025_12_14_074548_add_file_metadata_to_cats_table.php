<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cats', function (Blueprint $table) {
            $table->string('filename')->nullable()->after('filer');
            $table->string('mime')->nullable()->after('filename');
            $table->string('sizer')->nullable()->after('mime');
            $table->string('extension')->nullable()->after('sizer');
        });
    }

    public function down(): void
    {
        Schema::table('cats', function (Blueprint $table) {
            $table->dropColumn(['filename', 'mime', 'sizer', 'extension']);
        });
    }
};
