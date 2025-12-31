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
        Schema::create('litellm_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type'); // logs, usage, costs
            $table->string('status')->default('running'); // success, failed, running
            $table->integer('records_synced')->default(0);
            $table->string('last_synced_id')->nullable(); // LiteLLM'den son sync edilen ID
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('sync_type');
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('litellm_sync_logs');
    }
};
