@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Залишити коментар</h2>

        {{-- Flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрити"></button>
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Форма --}}
        <form class="needs-validation" action="{{ route('comments.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <input type="hidden" name="parent_id" value="{{ $parentId ?? '' }}">

            <div class="mb-3">
                <label for="username" class="form-label">Ім’я</label>
                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" required>
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Ел. пошта</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="homepage" class="form-label">Домашня сторінка (необов'язково)</label>
                <input type="url" name="homepage" id="homepage" class="form-control @error('homepage') is-invalid @enderror"
                       value="{{ old('homepage') }}">
                @error('homepage')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="text" class="form-label">Коментар</label>
                <div class="mb-2">
                    <button type="button" onclick="insertTag('strong')">B</button>
                    <button type="button" onclick="insertTag('i')">I</button>
                    <button type="button" onclick="insertTag('code')">Code</button>
                    <button type="button" onclick="insertLink()">Link</button>
                </div>

                <textarea name="text" id="text" rows="4" class="form-control @error('text') is-invalid @enderror" required>{{ old('text') }}</textarea>
                @error('text')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="attachment" class="form-label">Файл (необов'язково)</label>
                <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror">
                @error('attachment')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="image-preview" class="mt-3">
                    <img src="#" alt="Попередній перегляд" class="img-fluid rounded d-none" id="preview-image">
                </div>
            </div>

            <div class="mb-3">
                <label for="parent_id" class="form-label">Відповідь на:</label>
                <select name="parent_id" id="parent_id" class="form-select">
                    <option value="">-- Кореневий коментар --</option>
                    @foreach($comments as $comment)
                        <option value="{{ $comment->id }}" {{ old('parent_id') == $comment->id ? 'selected' : '' }}>
                            {{ $comment->username }} — {{ Str::limit($comment->text, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                {!! NoCaptcha::display(['data-theme' => 'light']) !!}
                @error('captcha')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Надіслати</button>
        </form>
    </div>
@endsection

@push('scripts')
    {!! NoCaptcha::renderJs() !!}

    <script>
        document.getElementById('attachment').addEventListener('change', function (event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview-image');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.classList.add('d-none');
            }
        });
    </script>

@endpush
