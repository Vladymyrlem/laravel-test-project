import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import {createApp} from 'vue';
import axios from 'axios';

import CommentForm from './components/CommentForm.vue'
import Comment from './components/AnswerForm.vue'

const app = createApp(CommentForm)
app.mount('#vue-app')

const comment = createApp(Comment)
// comment.component('comment', Comment)
comment.mount('#comment')
function insertTag(tag) {
    const textarea = document.getElementById('text');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    const wrapped = `<${tag}>${selected}</${tag}>`;

    textarea.setRangeText(wrapped, start, end, 'end');
    textarea.focus();
}

function insertLink() {
    const url = prompt("Введіть URL:");
    if (!url) return;
    const textarea = document.getElementById('text');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end) || "посилання";
    const wrapped = `<a href="${url}">${selected}</a>`;

    textarea.setRangeText(wrapped, start, end, 'end');
    textarea.focus();
}

document.addEventListener('DOMContentLoaded', () => {
    const attachment = document.getElementById('attachment');
    if (attachment) {
        attachment.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('preview-image');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
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
