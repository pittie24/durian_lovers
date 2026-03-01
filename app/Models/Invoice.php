<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'invoice_number',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXXXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', today())
                         ->latest('id')
                         ->first();
        
        $sequence = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -6) + 1 : 1;
        
        return 'INV-' . $date . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
}
