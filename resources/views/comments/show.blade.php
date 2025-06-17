{{-- resources/views/comments/show.blade.php --}}

@extends('layouts.app')

@section('content')
    {{-- resources/views/comments/show.blade.php --}}
    <div id="vue-app" class="mb-3">
    </div>
    <div class="container-lg">
        <div class="row mb-3">
            <div class="col">
                <a href="{{ route('comments.index') }}" class="btn btn-sm btn-outline-secondary">
                    ← Назад до всіх коментарів
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h4>Відповіді на коментар</h4>
            </div>
        </div>

        @foreach($comment->children as $reply)
            @include('comments.partials.comment', ['comment' => $reply])
        @endforeach
    </div>


@endsection
