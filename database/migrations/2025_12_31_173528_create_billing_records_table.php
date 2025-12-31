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
        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_requests')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('stripe_invoice_id')->nullable();
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('subscription_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};
