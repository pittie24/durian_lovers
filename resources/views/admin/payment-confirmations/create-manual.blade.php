@extends('layouts.admin')

@section('content')
<div class="manual-order-page">
    <div class="page-head">
        <div>
            <h2>Buat Pesanan Cash</h2>
            <p>Masukkan data pelanggan manual, pilih produk, lalu simpan sebagai transaksi cash yang langsung lunas.</p>
        </div>
        <a href="{{ route('admin.payment-confirmations.index') }}" class="btn btn-outline">Kembali ke Pesanan</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.payment-confirmations.manual.store') }}" method="POST" class="manual-order-form">
        @csrf

        <div class="grid two-columns">
            <div class="card">
                <div class="card-body">
                    <h4>Data Pelanggan</h4>

                    <div class="form-group">
                        <label for="customer_name">Nama Pelanggan</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Email (Opsional)</label>
                        <input type="email" id="customer_email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">No. Telepon (Opsional)</label>
                        <input type="text" id="customer_phone" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4>Pengiriman & Pembayaran</h4>

                    <div class="form-group">
                        <label for="shipping_method">Metode Pengiriman</label>
                        <select id="shipping_method" name="shipping_method" class="form-control">
                            <option value="pickup" {{ old('shipping_method', 'pickup') === 'pickup' ? 'selected' : '' }}>Ambil di Toko</option>
                            <option value="delivery" {{ old('shipping_method') === 'delivery' ? 'selected' : '' }}>Dikirim</option>
                        </select>
                    </div>

                    <div class="form-group" id="shipping-address-group">
                        <label for="shipping_address">Alamat Pengiriman</label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" placeholder="Isi jika pesanan perlu dikirim">{{ old('shipping_address') }}</textarea>
                    </div>

                    <div class="cash-note">
                        <span class="cash-pill">Cash</span>
                        <p>Pembayaran dicatat langsung lunas. Bukti transfer tidak diperlukan.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="section-head">
                    <h4>Pilih Produk</h4>
                    <span>Isi jumlah untuk produk yang ingin dimasukkan ke pesanan.</span>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            max="{{ $product->stock }}"
                                            name="quantities[{{ $product->id }}]"
                                            value="{{ old('quantities.' . $product->id, 0) }}"
                                            class="qty-input"
                                            {{ $product->stock < 1 ? 'disabled' : '' }}
                                        >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada produk tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Pesanan Cash</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        const shippingSelect = document.getElementById('shipping_method');
        const addressGroup = document.getElementById('shipping-address-group');
        const addressInput = document.getElementById('shipping_address');

        function syncShippingField() {
            const isDelivery = shippingSelect.value === 'delivery';
            addressInput.required = isDelivery;
            addressGroup.style.display = isDelivery ? 'block' : 'none';
        }

        shippingSelect.addEventListener('change', syncShippingField);
        syncShippingField();
    })();
</script>

<style>
.manual-order-page h2 {
    margin: 0;
    color: #1f2937;
}

.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
}

.page-head p {
    margin: 6px 0 0;
    color: #6b7280;
}

.grid.two-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
}

.card-body {
    padding: 22px;
}

.card h4 {
    margin: 0 0 16px;
    color: #111827;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 700;
    color: #374151;
}

.form-control,
.qty-input {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    font: inherit;
}

.qty-input {
    max-width: 88px;
    text-align: center;
}

.cash-note {
    margin-top: 18px;
    padding: 14px 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, #fff7ed, #fffbeb);
    border: 1px solid #fed7aa;
}

.cash-note p {
    margin: 10px 0 0;
    color: #9a3412;
}

.cash-pill {
    display: inline-flex;
    padding: 6px 12px;
    border-radius: 999px;
    background: #f59e0b;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.section-head span {
    color: #6b7280;
    font-size: 13px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}

.table th {
    background: #fff8db;
    color: #92400e;
    font-weight: 800;
}

.table-wrap {
    overflow-x: auto;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

.btn {
    padding: 11px 18px;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}

.btn-outline {
    background: #fff;
    border: 1px solid #d1d5db;
    color: #374151;
}

.alert {
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.mt-4 {
    margin-top: 16px;
}

.text-center {
    text-align: center;
}

@media (max-width: 900px) {
    .page-head,
    .section-head,
    .grid.two-columns {
        display: block;
    }

    .page-head a,
    .grid.two-columns .card:first-child {
        margin-bottom: 16px;
    }
}
</style>
@endsection
