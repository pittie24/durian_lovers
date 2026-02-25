@extends('layouts.admin')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp

<div class="section-heading">
    <h2>Manajemen Produk</h2>
    <a href="/admin/produk/tambah" class="btn primary">
        <i class="bi bi-plus-lg"></i> Tambah Produk
    </a>
</div>

<div class="grid cards">
    @foreach ($products as $product)
        @php
          $img = $product->image_url ?? '';
          // kalau sudah http/https, pakai langsung. kalau tidak, pakai asset()
          $imgSrc = Str::startsWith($img, ['http://','https://'])
              ? $img
              : asset(ltrim($img, '/'));
        @endphp

        <div class="card product-card">
            <img src="{{ $imgSrc }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';">

            <div class="card-body">
                <h4>{{ $product->name }}</h4>

                <p class="muted">{{ $product->description }}</p>

                <div class="price">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </div>

                <!-- stok & terjual -->
                <div class="meta-row">
                    <span>Stok: {{ $product->stock }}</span>
                    <span>Terjual: {{ $product->sold_count }}</span>
                </div>

                <!-- tombol -->
                <div class="card-actions">
                    <a href="/admin/produk/{{ $product->id }}/edit"
                       class="btn btn-edit">
                        <i class="bi bi-pencil-square"></i>
                        Edit
                    </a>

                    <form method="POST"
                          action="/admin/produk/{{ $product->id }}"
                          class="inline-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-delete"
                                onclick="return confirm('Yakin hapus produk ini?')">
                            <i class="bi bi-trash"></i>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection