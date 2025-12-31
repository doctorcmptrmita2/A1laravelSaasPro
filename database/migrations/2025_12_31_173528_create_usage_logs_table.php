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
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('api_key_id')->nullable()->constrained()->onDelete('set null');
            $table->string('litellm_log_id')->nullable()->unique(); // LiteLLM'deki log ID - sync için
            $table->string('endpoint');
            $table->string('method')->default('POST');
            $table->integer('status_code')->nullable();
            $table->integer('response_time')->nullable(); // milliseconds
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->json('metadata')->nullable(); // model, user_id, etc.
            $table->timestamp('created_at'); // LiteLLM'den gelen timestamp
            $table->timestamp('synced_at')->nullable(); // Laravel'de sync edilme zamanı
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('api_key_id');
            $table->index('litellm_log_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
