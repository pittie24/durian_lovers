<table class="table-clean">
  <thead>
    <tr>
      <th>Nama</th>
      <th>Email</th>
      <th>Telepon</th>
      <th class="center-col">Total Pesanan</th>
      <th class="center-col">Total Pengeluaran</th>
      <th class="center-col">Terakhir Belanja</th>
      <th class="th-actions">Aksi</th>
    </tr>
  </thead>

  <tbody>
    @forelse ($customers as $customer)
      <tr>
        <td>{{ $customer->display_name }}</td>
        <td class="muted">{{ $customer->display_email }}</td>
        <td>{{ $customer->display_phone }}</td>

        <td class="center-col">{{ $customer->total_pesanan ?? 0 }}</td>
        <td class="center-col">Rp {{ number_format($customer->total_pengeluaran ?? 0, 0, ',', '.') }}</td>
        <td class="center-col">
          {{ $customer->terakhir_belanja
              ? \Carbon\Carbon::parse($customer->terakhir_belanja)->format('d M Y')
              : '-' }}
        </td>

        <td class="td-actions">
          <a href="/admin/pelanggan/{{ $customer->id }}" class="link-detail">
            Detail
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
