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

const { comments } = defineProps({
    comments: {
        type: Array,
        default: () => []
    }
})


let modalInstance = null

const openModal = () => {
    if (!modalInstance) {
        const modalEl = document.getElementById('commentAnswerModal')
        modalInstance = new bootstrap.Modal(modalEl)
    }
    modalInstance.show()
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

const submit = async () => {
    errors.value = {}
    const formData = new FormData()
    formData.append('username', username.value)
    formData.append('email', email.value)
    formData.append('homepage', homepage.value)
    formData.append('text', text.value)
    formData.append('parent_id', parent_id.value)
    formData.append('g-recaptcha-response', grecaptcha.getResponse())

    if (attachment.value) {
        formData.append('attachment', attachment.value)
    }

    try {
        const response = await axios.post('/comments', formData)

        // Очистити
        username.value = ''
        email.value = ''
        homepage.value = ''
        text.value = ''
        parent_id.value = ''
        attachment.value = null
        imagePreview.value = null
        grecaptcha.reset()

        modalInstance.hide()

        // Показати toast
        const toastEl = document.getElementById('success-toast')
        const toast = new Toast(toastEl)
        toast.show()
    } catch (e) {
        if (e.response?.data?.errors) {
            errors.value = e.response.data.errors
        } else {
            alert('Помилка надсилання')
        }
    }
    const replyTo = (id) => {
        parent_id.value = id
        openModal()
    }

}


</script>

<template>
    <button class="btn btn-primary" @click="openModal">Відповісти</button>
    <input type="hidden" v-model="parent_id">

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


    <div class="modal fade" id="commentAnswerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Відповідь на коментар</h5>
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
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LcLH2ArAAAAAHDjs2rugsVBy5_7lsqWQeaA3cVU"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="submit" class="btn btn-primary">Надіслати</button>
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
