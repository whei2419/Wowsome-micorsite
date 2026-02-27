<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to ensure compatibility if doctrine/dbal is not installed.
        DB::statement("ALTER TABLE `users` MODIFY `country` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `country` VARCHAR(255) NOT NULL");
    }
};
