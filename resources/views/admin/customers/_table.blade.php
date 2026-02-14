<table class="table-clean">
  <thead>
    <tr>
      <th>Nama</th>
      <th>Email</th>
      <th>Telepon</th>
      <th>Total Pesanan</th>
      <th>Total Pengeluaran</th>
      <th>Terakhir Belanja</th>
      <th class="th-actions">Aksi</th>
    </tr>
  </thead>

  <tbody>
    @forelse ($customers as $customer)
      <tr>
        <td>{{ $customer->name }}</td>
        <td class="muted">{{ $customer->email }}</td>
        <td>{{ $customer->phone ?? '-' }}</td>

        <td>{{ $customer->total_pesanan ?? 0 }}</td>
        <td>Rp {{ number_format($customer->total_pengeluaran ?? 0, 0, ',', '.') }}</td>
        <td>
          {{ $customer->terakhir_belanja
              ? \Carbon\Carbon::parse($customer->terakhir_belanja)->format('d M Y')
              : '-' }}
        </td>

        <td class="td-actions">
          <a href="/admin/pelanggan/{{ $customer->id }}" class="link-detail">
            <i class="bi bi-eye"></i> Detail
          </a>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" class="empty">
          @if($q)
            Pelanggan tidak ditemukan
          @else
            Tidak ada pelanggan
          @endif
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

<div class="pagination-wrap">
  {{ $customers->links() }}
</div>