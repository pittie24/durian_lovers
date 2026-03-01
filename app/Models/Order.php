<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'shipping_method',
        'payment_method',
        'phone',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'total' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function paymentConfirmation()
    {
        return $this->hasOne(PaymentConfirmation::class);
    }

    public function getOrderNumberAttribute(): string
    {
        return 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getTotalAmountAttribute(): int
    {
        return $this->total;
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer_name
            ?: ($this->user?->name ?? 'Pelanggan');
    }

    public function getCustomerDisplayEmailAttribute(): string
    {
        return $this->customer_email
            ?: ($this->user?->email ?? '-');
    }

    public function getCustomerDisplayPhoneAttribute(): string
    {
        return $this->customer_phone
            ?: ($this->user?->phone ?? $this->phone ?? '-');
    }
}
