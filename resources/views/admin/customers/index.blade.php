@extends('layouts.admin')

@section('content')
<div class="page-head">
  <h1 class="page-title">Data Pelanggan</h1>
</div>

{{-- Summary Cards --}}
<div class="summary-grid">
  <div class="summary-card">
    <div class="summary-icon icon-blue">
      <i class="bi bi-people"></i>
    </div>
    <div>
      <div class="summary-label">Total Pelanggan</div>
      <div class="summary-value">{{ $totalPelanggan ?? 0 }}</div>
    </div>
  </div>

  <div class="summary-card">
    <div class="summary-icon icon-green">
      <i class="bi bi-graph-up"></i>
    </div>
    <div>
      <div class="summary-label">Pelanggan Aktif</div>
      <div class="summary-value">{{ $pelangganAktif ?? 0 }}</div>
    </div>
  </div>

  <div class="summary-card">
    <div class="summary-icon icon-yellow">
      <i class="bi bi-receipt"></i>
    </div>
    <div>
      <div class="summary-label">Rata-rata Pembelian</div>
      <div class="summary-value">
        Rp {{ number_format($rataRataPembelian ?? 0, 0, ',', '.') }}
      </div>
    </div>
  </div>
</div>

{{-- Search --}}
<form class="search-row" id="searchForm" method="GET" action="{{ url()->current() }}">
  <div class="search-box">
    <span class="search-icon"><i class="bi bi-search"></i></span>
    <input
      type="text"
      id="searchInput"
      name="q"
      value="{{ $q ?? '' }}"
      placeholder="Cari pelanggan..."
      autocomplete="off"
    />
  </div>
</form>

{{-- Table --}}
<div class="table-card">
  @include('admin.customers._table')
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableCard = document.querySelector('.table-card');
    
    let debounceTimer;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(this.value);
        }, 300); // Delay 300ms after user stops typing
    });
    
    function performSearch(query) {
        // Show loading indicator
        tableCard.innerHTML = '<div class="loading">Memuat...</div>';
        
        // Build the search URL
        let url = window.location.pathname + '?q=' + encodeURIComponent(query);
        
        // Preserve other query parameters if needed
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams) {
            if (key !== 'q') {
                url += '&' + key + '=' + encodeURIComponent(value);
            }
        }
        
        // Perform AJAX request
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update the table card with new content
            tableCard.innerHTML = html;
            
            // Reattach event listeners to new pagination elements
            attachPaginationListeners();
        })
        .catch(error => {
            console.error('Error during search:', error);
            tableCard.innerHTML = '<div class="error">Terjadi kesalahan saat mencari</div>';
        });
    }
    
    function attachPaginationListeners() {
        const paginationLinks = document.querySelectorAll('.pagination-wrap a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableCard.innerHTML = html;
                    attachPaginationListeners(); // Reattach for new pagination
                })
                .catch(error => {
                    console.error('Error during pagination:', error);
                    tableCard.innerHTML = '<div class="error">Terjadi kesalahan saat memuat halaman</div>';
                });
            });
        });
    }
    
    // Initialize pagination listeners
    attachPaginationListeners();
});
</script>

@endsection
