<div class="container-lg">
    <div class="row">
        @foreach($comments as $comment)
            @include('comments.partials.comment', ['comment' => $comment])
        @endforeach
    </div>

    <div class="row mt-3">
        <div class="col">
            {{ $comments->links() }}
        </div>
    </div>
</div>
