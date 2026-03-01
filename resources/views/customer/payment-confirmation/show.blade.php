@extends('layouts.app')

@section('content')
<div class="payment-confirmation-page">
    <h2>Konfirmasi Pembayaran</h2>

    {{-- Order Info --}}
    <div class="card">
        <div class="card-body">
            <h4>Detail Pesanan #{{ $order->order_number }}</h4>
            <div class="summary-row">
                <span>Total Pembayaran</span>
                <span class="text-primary font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Status</span>
                <span>{{ $order->status }}</span>
            </div>
        </div>
    </div>

    {{-- Bank/E-wallet Info --}}
    <div class="card mt-4">
        <div class="card-body">
            <h4>Informasi Pembayaran</h4>
            <p class="text-muted">Silakan transfer ke:</p>
            
            <div class="bank-account-item">
                <div class="bank-icon">🏦</div>
                <div class="bank-details">
                    <div class="bank-name">BRI</div>
                    <div class="bank-number">341901058068539</div>
                    <div class="bank-holder">a.n Durian Lovers</div>
                </div>
                <button type="button" class="btn-copy" onclick="copyToClipboard('341901058068539')">
                    📋 Copy
                </button>
            </div>

            <div class="bank-account-item">
                <div class="bank-icon">📱</div>
                <div class="bank-details">
                    <div class="bank-name">DANA</div>
                    <div class="bank-number">081352953905</div>
                    <div class="bank-holder">a.n Durian Lovers</div>
                </div>
                <button type="button" class="btn-copy" onclick="copyToClipboard('081352953905')">
                    📋 Copy
                </button>
            </div>

            <div class="alert alert-info mt-3">
                <strong>📌 Penting:</strong>
                <ul class="mb-0 mt-2">
                    <li>Pastikan nominal transfer sesuai dengan total pembayaran.</li>
                    <li>Gunakan nama Anda saat melakukan transfer.</li>
                    <li>Simpan bukti transfer dengan baik.</li>
                    <li>Upload bukti transfer dalam format JPG/PNG (max 2MB).</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Confirmation Status --}}
    <div class="card mt-4">
        <div class="card-body">
            <h4>Upload Bukti Pembayaran</h4>
            
            @if(!$confirmation)
                {{-- Belum upload --}}
                <form action="{{ route('pembayaran.confirmation.store', $order) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    
                    <div class="form-group">
                        <label for="proof_image">📷 Upload Bukti Transfer</label>
                        <input type="file" name="proof_image" id="proof_image" class="form-control" accept="image/*" required>
                        @error('proof_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bank_name">Metode Pembayaran</label>
                        <select name="bank_name" id="bank_name" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="BRI">Transfer BRI</option>
                            <option value="DANA">DANA</option>
                        </select>
                        @error('bank_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="account_name">Nama Pengirim</label>
                        <input type="text" name="account_name" id="account_name" class="form-control" value="{{ auth()->user()->name }}" required>
                        @error('account_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="transfer_amount">Nominal Transfer (Rp)</label>
                        <input type="number" name="transfer_amount" id="transfer_amount" class="form-control" value="{{ old('transfer_amount', $order->total) }}" required>
                        @error('transfer_amount')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        📤 Upload Bukti Pembayaran
                    </button>
                </form>

            @elseif($confirmation->isPending())
                {{-- Menunggu verifikasi --}}
                <div class="status-box status-warning">
                    <div class="status-icon">⏳</div>
                    <div class="status-text">
                        <strong>Menunggu Verifikasi Admin</strong>
                        <p>Bukti pembayaran Anda sedang diverifikasi oleh admin.</p>
                    </div>
                </div>

            @elseif($confirmation->isApproved())
                {{-- Disetujui --}}
                <div class="status-box status-success">
                    <div class="status-icon">✅</div>
                    <div class="status-text">
                        <strong>Pembayaran Diverifikasi!</strong>
                        <p>Terima kasih! Pembayaran Anda telah diverifikasi.</p>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('customer.invoice.download', $order->id) }}" class="btn btn-success">
                        📄 Download Invoice
                    </a>
                </div>

            @elseif($confirmation->isRejected())
                {{-- Ditolak --}}
                <div class="status-box status-danger">
                    <div class="status-icon">❌</div>
                    <div class="status-text">
                        <strong>Konfirmasi Ditolak</strong>
                        <p>Alasan: {{ $confirmation->notes }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('pembayaran.confirmation.resubmit', $order->id) }}" class="btn btn-warning">
                        🔄 Ajukan Ulang
                    </a>
                </div>

            @endif
        </div>
    </div>
</div>

<style>
.payment-confirmation-page h2 { margin-bottom: 24px; color: #2c3e50; }
.bank-account-item { display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8f9fa; border-radius: 8px; margin-bottom: 12px; }
.bank-icon { font-size: 32px; }
.bank-details { flex: 1; }
.bank-name { font-weight: 700; color: #2c3e50; }
.bank-number { font-size: 18px; font-weight: 600; color: #27ae60; font-family: monospace; }
.bank-holder { font-size: 13px; color: #666; }
.btn-copy { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-copy:hover { background: #219a52; }
.status-box { display: flex; gap: 16px; padding: 20px; border-radius: 12px; align-items: flex-start; margin-top: 16px; }
.status-box.status-warning { background: #e3f2fd; border: 1px solid #2196f3; }
.status-box.status-success { background: #d4edda; border: 1px solid #28a745; }
.status-box.status-danger { background: #f8d7da; border: 1px solid #dc3545; }
.status-icon { font-size: 40px; }
.status-text strong { display: block; font-size: 16px; margin-bottom: 4px; }
.status-text p { margin: 0; color: #666; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50; }
.form-control { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
.btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
.btn-primary { background: #27ae60; color: white; }
.btn-primary:hover { background: #219a52; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: #333; }
.text-primary { color: #27ae60; }
.font-weight-bold { font-weight: 700; }
.text-danger { color: #dc3545; font-size: 13px; }
.alert { padding: 16px; border-radius: 8px; margin-bottom: 16px; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Nomor berhasil dicopy: ' + text);
    }, function(err) {
        console.error('Gagal copy: ', err);
    });
}
</script>
@endsection
