@extends('layouts.admin')

@section('content')
<h2>Tambah Produk</h2>

<form method="POST" action="/admin/produk" class="card">
    @csrf
    <div class="card-body">
        <label>Nama Produk</label>
        <input type="text" name="name" required>
        <label>Kategori</label>
        <input type="text" name="category" required>
        <label>Deskripsi</label>
        <textarea name="description" rows="3"></textarea>
        <label>Komposisi</label>
        <input type="text" name="composition">
        <label>Berat/Ukuran</label>
        <input type="text" name="weight">
        <label>Harga</label>
        <input type="number" name="price" min="0" required>
        <label>Stok</label>
        <input type="number" name="stock" min="0" required>
        <label>URL Gambar</label>
        <input type="text" name="image_url">
        <button type="submit" class="btn primary">Simpan Produk</button>
    </div>
</form>
@endsection
