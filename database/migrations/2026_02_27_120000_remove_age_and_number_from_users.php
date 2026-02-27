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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop columns if they exist
                if (Schema::hasColumn('users', 'age')) {
                    $table->dropColumn('age');
                }
                if (Schema::hasColumn('users', 'number')) {
                    $table->dropColumn('number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-create columns as nullable strings
            if (!Schema::hasColumn('users', 'age')) {
                $table->string('age')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'number')) {
                $table->string('number')->nullable()->after('age');
            }
        });
    }
};
