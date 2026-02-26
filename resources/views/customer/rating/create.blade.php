@extends('layouts.app')

@section('content')
@php
    $selectedStars = old('stars', $existing->stars ?? 5);
@endphp

<div class="card rating-card">
    <div class="card-body">
        <h2 class="rating-title">Beri Rating</h2>
        <p class="rating-product">{{ $orderItem->product->name }}</p>

        <form method="POST" action="/rating/{{ $orderItem->id }}">
            @csrf

            <label class="rating-label">Rating Anda</label>

            <div class="star-input">
                <input type="radio" id="star5" name="stars" value="5" {{ $selectedStars == 5 ? 'checked' : '' }}>
                <label for="star5"></label>

                <input type="radio" id="star4" name="stars" value="4" {{ $selectedStars == 4 ? 'checked' : '' }}>
                <label for="star4"></label>

                <input type="radio" id="star3" name="stars" value="3" {{ $selectedStars == 3 ? 'checked' : '' }}>
                <label for="star3"></label>

                <input type="radio" id="star2" name="stars" value="2" {{ $selectedStars == 2 ? 'checked' : '' }}>
                <label for="star2"></label>

                <input type="radio" id="star1" name="stars" value="1" {{ $selectedStars == 1 ? 'checked' : '' }}>
                <label for="star1"></label>
            </div>

            @error('stars')
                <div class="form-error">{{ $message }}</div>
            @enderror

            <label class="rating-label">Komentar (opsional)</label>
            <textarea name="comment" rows="3" class="rating-textarea">{{ old('comment', $existing->comment ?? '') }}</textarea>

            <div class="form-actions">
                <a href="/riwayat" class="btn outline">Batal</a>
                <button type="submit" class="btn primary">Kirim Rating</button>
            </div>
        </form>
    </div>
</div>
@endsection