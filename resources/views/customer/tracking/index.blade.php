@extends('layouts.app')

@section('content')
<div class="tracking-list-page">
    <h2>📦 Status Pesanan</h2>

    @forelse($orders as $order)
        <div class="tracking-card">
            <div class="tracking-header">
                <div class="tracking-id">
                    <strong>Order #{{ $order->order_number }}</strong>
                    <span class="tracking-date">{{ $order->created_at->format('d M Y') }}</span>
                </div>
                <div class="tracking-status status-{{ strtolower(str_replace('_', '-', $order->status)) }}">
                    {{ str_replace('_', ' ', $order->status) }}
                </div>
            </div>

            <div class="tracking-body">
                <div class="tracking-items">
                    @foreach($order->items->take(3) as $item)
                        <div class="tracking-item">
                            <span class="item-name">{{ $item->product->name }}</span>
                            <span class="item-qty">x{{ $item->quantity }}</span>
                        </div>
                    @endforeach
                    @if($order->items->count() > 3)
                        <div class="tracking-more">+{{ $order->items->count() - 3 }} item lainnya</div>
                    @endif
                </div>

                <div class="tracking-footer">
                    <div class="tracking-total">
                        <span>Total:</span>
                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                    </div>
                    <a href="/tracking/{{ $order->id }}" class="btn-tracking">
                        👁 Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="tracking-empty">
            <p>Belum ada pesanan untuk ditrack.</p>
            <a href="/produk" class="btn-shop">🛒 Belanja Sekarang</a>
        </div>
    @endforelse
</div>

<style>
.tracking-list-page { max-width: 800px; margin: 0 auto; }
.tracking-list-page h2 { font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 24px; }

.tracking-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

.tracking-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #eee; }
.tracking-id strong { display: block; font-size: 16px; color: #2c3e50; }
.tracking-date { font-size: 13px; color: #666; }

.tracking-status { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.tracking-status.status-menunggu-pembayaran { background: #fff3cd; color: #856404; }
.tracking-status.status-sedang-diproses, .tracking-status.status-pesanan-diterima { background: #d1ecf1; color: #0c5460; }
.tracking-status.status-siap-diambil-dikirim { background: #d4edda; color: #155724; }
.tracking-status.status-selesai { background: #27ae60; color: white; }
.tracking-status.status-dibatalkan { background: #dc3545; color: white; }

.tracking-items { margin-bottom: 16px; }
.tracking-item { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
.item-name { color: #2c3e50; }
.item-qty { color: #666; }
.tracking-more { font-size: 13px; color: #666; font-style: italic; }

.tracking-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #eee; }
.tracking-total { font-size: 14px; }
.tracking-total strong { color: #27ae60; font-size: 16px; }

.btn-tracking { padding: 10px 20px; background: #27ae60; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
.btn-tracking:hover { background: #219a52; }

.tracking-empty { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; }
.tracking-empty p { color: #666; margin-bottom: 20px; }
.btn-shop { padding: 12px 24px; background: #27ae60; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; }
</style>
@endsection
