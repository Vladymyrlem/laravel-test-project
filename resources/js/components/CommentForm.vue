<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { onMounted } from 'vue'
import { Toast } from 'bootstrap'

const username = ref('')
const email = ref('')
const homepage = ref('')
const text = ref('')
const parent_id = ref('')
const attachment = ref(null)
const errors = ref({})
const imagePreview = ref(null)
const editComment = ref(null)

const { comments } = defineProps({
    comments: {
        type: Array,
        default: () => []
    }
})


let modalInstance = null

const openModal = () => {
    if (!modalInstance) {
        const modalEl = document.getElementById('commentModal')
        modalInstance = new bootstrap.Modal(modalEl)
    }
    modalInstance.show()
}
const openEditModal = (comment) => {
    editComment.value = comment
    parent_id.value = comment.parent_id || ''
    username.value = comment.user_name
    email.value = comment.email
    homepage.value = comment.homepage
    text.value = comment.text
    imagePreview.value = comment.attachment_url || null
    attachment.value = null // щоб не затирався старий файл

    openModal()
}

const replyTo = (id) => {
    parent_id.value = id
    openModal()
}
const handleFileChange = (event) => {
    const file = event.target.files[0]
    attachment.value = file

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    } else {
        imagePreview.value = null
    }
}

const insertTag = (tag) => {
    const textarea = document.getElementById('text')
    const start = textarea.selectionStart
    const end = textarea.selectionEnd
    const selected = text.value.substring(start, end)
    const wrapped = `<${tag}>${selected}</${tag}>`
    text.value = text.value.substring(0, start) + wrapped + text.value.substring(end)
}
const cancelEdit = () => {
    editComment.value = null
    username.value = ''
    email.value = ''
    homepage.value = ''
    text.value = ''
    parent_id.value = ''
    attachment.value = null
    imagePreview.value = null
}

const submit = async () => {
    errors.value = {}
    const formData = new FormData()
    formData.append('user_name', username.value)
    formData.append('content', text.value)
    formData.append('homepage', homepage.value)
    formData.append('parent_id', parent_id.value)
    formData.append('g-recaptcha-response', grecaptcha.getResponse())

    if (attachment.value) {
        formData.append('attachment', attachment.value)
    }

    try {
        let response;
        if (editComment.value) {
            formData.append('_method', 'PUT');
            // Редагування — PUT запит на comments/{id}
            response = await axios.post(`/comments/${editComment.value.id}`, formData, {
                headers: { 'X-HTTP-Method-Override': 'PUT' }
            })
        } else {
            // Створення — POST запит на comments
            response = await axios.post('/comments', formData)
        }

        // Очистити форму
        username.value = ''
        email.value = ''
        homepage.value = ''
        text.value = ''
        parent_id.value = ''
        attachment.value = null
        imagePreview.value = null
        grecaptcha.reset()

        editComment.value = null
        modalInstance.hide()

        // Показати toast успіху
        const toastEl = document.getElementById('success-toast')
        const toast = new Toast(toastEl)
        toast.show()
    } catch (e) {
        if (e.response?.data?.errors) {
            console.log('Validation errors:', e.response.data.errors); // додай для дебагу
            errors.value = e.response.data.errors
        } else {
            alert('Помилка надсилання')
        }
    }
}

onMounted(() => {
    window.replyTo = replyTo
    window.editComment = openEditModal
})



</script>

<template>
    <h5 class="modal-title">
        {{ editComment ? 'Редагувати коментар' : (parent_id ? 'Відповідь на коментар' : 'Залишити коментар') }}
    </h5>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="success-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ✅ Коментар успішно надіслано!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрити"></button>
            </div>
        </div>
    </div>
    <input type="hidden" v-model="parent_id">


    <div class="modal fade" id="commentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ editComment ? 'Редагувати коментар' : (parent_id ? 'Відповідь на коментар' : 'Залишити коментар') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
                </div>
                <div class="modal-body">
                    <div v-if="Object.keys(errors).length" class="alert alert-danger">
                        <ul class="mb-0">
                            <li v-for="(errMsgs, field) in errors" :key="field">{{ errMsgs[0] }}</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ім’я</label>
                        <input v-model="username" type="text" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input v-model="email" type="email" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Домашня сторінка</label>
                        <input v-model="homepage" type="url" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Коментар</label>
                        <div class="mb-2">
                            <button type="button" @click="insertTag('strong')">B</button>
                            <button type="button" @click="insertTag('i')">I</button>
                            <button type="button" @click="insertTag('code')">Code</button>
                            <button type="button" @click="insertTag('a')">Link</button>
                        </div>
                        <textarea v-model="text" id="text" rows="4" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="attachment" class="form-label">Прикріпити файл</label>
                        <input type="file" name="attachment" id="attachment" class="form-control" @change="handleFileChange" />
                        <div id="image-preview" class="mt-2">
                            <img v-if="imagePreview" :src="imagePreview" alt="Превʼю" class="img-fluid rounded" />
                        </div>
                    </div>


                    <div class="mb-3" v-if="!parent_id">
                        <label class="form-label">Відповідь на:</label>
                        <select v-model="parent_id" class="form-select">
                            <option value="">-- Кореневий коментар --</option>
                            <option v-for="comment in comments" :key="comment.id" :value="comment.id">
                                {{ comment.user_name }} — {{ comment.text.slice(0, 30) }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LcLH2ArAAAAAHDjs2rugsVBy5_7lsqWQeaA3cVU"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button v-if="editComment" class="btn btn-outline-secondary me-auto" @click="cancelEdit">
                        Скасувати редагування
                    </button>
                    <button @click="submit" class="btn btn-primary">
                        {{ editComment ? 'Зберегти зміни' : 'Надіслати' }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
img {
    max-height: 200px;
}
#success-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1055;
    min-width: 250px;
}

</style>
