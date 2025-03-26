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
        Schema::table('events', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->string('image')->nullable()->change();
            $table->enum('status', ['upcoming','ongoing','completed','cancelled'])->default('upcoming')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('description')->change();
            $table->dropColumn('image')->change();
            $table->dropColumn('status')->change();
        });
    }
};
