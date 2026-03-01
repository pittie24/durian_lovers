<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'proof_image',
        'bank_name',
        'account_name',
        'transfer_amount',
        'status',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'transfer_amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    /**
     * Check if confirmation is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if confirmation is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if confirmation is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the confirmation and update order
     */
    public function approve(int $adminId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'verified_by' => $adminId,
            'verified_at' => now(),
        ]);

        // Setelah pembayaran valid, pesanan masuk ke tahap proses admin.
        $this->order->update([
            'status' => 'SEDANG_DIPROSES',
        ]);

        // Update or create payment record
        $payment = $this->order->payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $this->order->id,
                'provider' => 'manual_transfer',
                'status' => 'PAID',
                'payment_method' => $this->bank_name,
                'paid_at' => now(),
            ]);
        } else {
            $payment->update([
                'status' => 'PAID',
                'payment_method' => $this->bank_name,
                'paid_at' => now(),
            ]);
        }

        // Generate invoice if not exists
        if (!$this->order->invoice) {
            try {
                \App\Services\InvoiceGeneratorService::generate($this->order, $payment);
            } catch (\Throwable $e) {
                \Log::error('Failed to generate invoice after confirmation approval', [
                    'order_id' => $this->order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Reject the confirmation
     */
    public function reject(int $adminId, string $notes): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_by' => $adminId,
            'verified_at' => now(),
            'notes' => $notes,
        ]);

        // Restore stock
        foreach ($this->order->items as $item) {
            \App\Models\Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }

        // Update order status
        $this->order->update([
            'status' => 'DIBATALKAN',
        ]);
    }
}
