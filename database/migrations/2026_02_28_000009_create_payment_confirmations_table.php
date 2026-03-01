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
        Schema::create('payment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('proof_image'); // Path to uploaded image
            $table->string('bank_name'); // Bank pengirim (BCA, Mandiri, dll)
            $table->string('account_name'); // Nama pengirim
            $table->decimal('transfer_amount', 12, 2); // Nominal transfer
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->text('notes')->nullable(); // Catatan dari admin jika reject
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_confirmations');
    }
};
