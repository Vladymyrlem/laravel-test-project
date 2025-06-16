@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Редагування коментаря</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('comments.update', $comment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="author" class="form-label">Ім'я</label>
                <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
                       value="{{ old('author', $comment->author) }}">
                @error('author')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">Коментар</label>
                <textarea name="content" rows="5"
                          class="form-control @error('content') is-invalid @enderror">{{ old('content', $comment->content) }}</textarea>
                @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="attachment" class="form-label">Новий файл (не обов'язково)</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                @error('attachment')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($comment->attachment_path)
                    <p class="mt-2">Поточний файл: <a href="{{ asset('storage/' . $comment->attachment_path) }}" target="_blank">переглянути</a></p>
                @endif
            </div>
            <div class="mb-3">
                {!! NoCaptcha::display() !!}
                @error('g-recaptcha-response')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Оновити</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Назад</a>
        </form>
    </div>
@endsection
