<div class="col-12" :class="'comment-' . $comment->id" data-comment-id="{{ $comment->id }}">
    <div class="card mb-3">
        <div class="card-body">
            <div class="meta d-flex align-items-center justify-content-start">
                <small class="text-muted">Creating {{ $comment->created_at->format('d.m.Y H:i') }}</small>&nbsp; by&nbsp;
                <a class="small-text" href="mailto:{{ $comment->email }}">{{ $comment->user_name }}</a>
                <button class="btn btn-sm btn-outline-secondary edit-button d-flex ms-auto" onclick="editComment({{ json_encode($comment) }})">
                    ✏️ Редагувати
                </button>
            </div>
            <div class="comment-content">
                {!! $comment->text !!}
            </div>
            @if($comment->media && $comment->media->isNotEmpty())
                @foreach($comment->media as $media)
                    <div class="mt-2">
                        @if($media->type === 'image')
                            <a href="{{ asset($media->file_url) }}" data-lightbox="img-{{ $comment->id }}">
                                <img src="{{ asset($media->file_url) }}" class="img-thumbnail" width="100" />
                            </a>
                        @elseif(in_array($media->type, ['text', 'pdf']))
                            <a href="{{ asset($media->file_url) }}" target="_blank">
                                {{ basename($media->file_url) }}
                            </a>
                        @endif
                    </div>
                @endforeach
            @endif


            @if(empty($comment->parent_id))
                <div class="mt-2">
                    {{--            <a href="{{ route('comments.create', ['parent_id' => $comment->id]) }}" class="btn btn-sm btn-outline-primary">Відповісти</a>--}}

                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button class="btn btn-sm btn-outline-primary reply-button d-flex" onclick="replyTo({{ $comment->id }})">
                        <span>Reply</span>
                        <svg fill="#000000" height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 330 330" xml:space="preserve"><script xmlns=""/>
                            <g id="XMLID_2_">
                                <path id="XMLID_4_" d="M184.373,114.292l0.016-58.487c0.002-6.067-3.651-11.538-9.256-13.86   c-5.602-2.322-12.056-1.042-16.348,3.247l-72.824,72.786c-5.859,5.857-5.862,15.354-0.006,21.213l72.786,72.826   c2.869,2.872,6.708,4.397,10.612,4.397c1.932,0,3.879-0.373,5.733-1.141c5.605-2.32,9.262-7.789,9.264-13.856l0.015-56.966   C249.356,151.905,300,207.244,300,274.199c0,8.284,6.716,15,15,15s15-6.716,15-15C330,190.69,265.934,121.875,184.373,114.292z"/>
                                <path id="XMLID_5_" d="M36.213,128.592L98.428,66.41c5.859-5.856,5.862-15.354,0.006-21.213c-5.857-5.86-15.354-5.861-21.213-0.005   L4.396,117.978c-5.859,5.857-5.862,15.354-0.006,21.213l72.787,72.826c2.929,2.931,6.77,4.396,10.609,4.396   c3.838,0,7.675-1.463,10.604-4.391c5.859-5.856,5.862-15.354,0.006-21.213L36.213,128.592z"/>
                            </g>
                            <script xmlns=""/></svg>
                    </button>
                </div>
            @endif
        </div>


        {{-- Рекурсія --}}
        @if($comment->children)
            <div class="ms-4">
                @foreach($comment->children as $reply)
                    @include('comments.partials.comment', ['comment' => $reply])
                @endforeach
            </div>
        @endif
    </div>
</div>
