@extends('layouts.admin')

@section('content')
<h2>Edit Produk</h2>

<form method="POST" action="/admin/produk/{{ $product->id }}" class="card">
    @csrf
    <div class="card-body">
        <label>Nama Produk</label>
        <input type="text" name="name" value="{{ $product->name }}" required>
        <label>Kategori</label>
        <input type="text" name="category" value="{{ $product->category }}" required>
        <label>Deskripsi</label>
        <textarea name="description" rows="3">{{ $product->description }}</textarea>
        <label>Komposisi</label>
        <input type="text" name="composition" value="{{ $product->composition }}">
        <label>Berat/Ukuran</label>
        <input type="text" name="weight" value="{{ $product->weight }}">
        <label>Harga</label>
        <input type="number" name="price" min="0" value="{{ $product->price }}" required>
        <label>Stok</label>
        <input type="number" name="stock" min="0" value="{{ $product->stock }}" required>
        <label>URL Gambar</label>
        <input type="text" name="image_url" value="{{ $product->image_url }}">
        <button type="submit" class="btn primary">Simpan Perubahan</button>
    </div>
</form>
@endsection
