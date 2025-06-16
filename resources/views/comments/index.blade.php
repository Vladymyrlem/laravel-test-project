@extends('layouts.app')
<!-- Vue-компонент -->


<div class="container-lg">
    <div id="vue-app" class="mb-3">
    </div>
    <form method="GET" id="sortForm" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="sort_by" class="form-select">
                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Дата</option>
                <option value="user_name" {{ request('sort_by') == 'user_name' ? 'selected' : '' }}>Ім'я</option>
                <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="sort_dir" class="form-select">
                <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Спадання</option>
                <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Зростання</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Сортувати</button>
        </div>
    </form>
</div>
<div class="container-lg">
    <div id="commentsContainer">
        @include('comments.partials.comments-list', ['comments' => $comments])
    </div>
</div>

{{ $comments->withQueryString()->links() }}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.initialComments = @json($comments);
    $(document).ready(function() {
        $('#sortForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('comments.index') }}",
                type: 'GET',
                data: $(this).serialize(),
                beforeSend: function() {
                    $('#commentsContainer').html('<p>Завантаження...</p>');
                },
                success: function(response) {
                    // Замінюємо тільки HTML частину коментарів
                    $('#commentsContainer').html(
                        $(response).find('#commentsContainer').html()
                    );
                },
                error: function() {
                    alert('Помилка при завантаженні.');
                }
            });
        });
    });
    $(document).on('click', '#commentsContainer .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');

        $.ajax({
            url: url,
            beforeSend: function() {
                $('#commentsContainer').html('<p>Завантаження...</p>');
            },
            success: function(response) {
                $('#commentsContainer').html(
                    $(response).find('#commentsContainer').html()
                );
            },
            error: function() {
                alert('Не вдалося завантажити сторінку.');
            }
        });
    });

</script>
