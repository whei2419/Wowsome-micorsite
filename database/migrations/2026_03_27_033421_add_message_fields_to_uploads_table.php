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
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
            $table->string('client_id')->nullable();
            $table->unsignedInteger('flower_id')->nullable();
            $table->string('flower_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'flower_id', 'flower_name', 'sender_name', 'message', 'sent_at']);
        });
    }
};
