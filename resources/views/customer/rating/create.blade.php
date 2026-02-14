@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h2>Beri Rating</h2>
        <p>{{ $orderItem->product->name }}</p>
        <form method="POST" action="/rating/{{ $orderItem->id }}">
            @csrf
            <label>Bintang (1-5)</label>
            <input type="number" name="stars" min="1" max="5" value="{{ $existing->stars ?? 5 }}" required>
            <label>Komentar (opsional)</label>
            <textarea name="comment" rows="3">{{ $existing->comment ?? '' }}</textarea>
            <div class="form-actions">
                <a href="/riwayat" class="btn outline">Batal</a>
                <button type="submit" class="btn primary">Kirim Rating</button>
            </div>
        </form>
    </div>
</div>
@endsection
