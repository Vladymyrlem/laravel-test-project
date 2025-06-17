<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { onMounted } from 'vue'
import { Toast } from 'bootstrap'
import DOMPurify from 'dompurify'
import { useRoute } from 'vue-router'

const username = ref('')
const email = ref('')
const homepage = ref('')
const text = ref('')
const parent_id = ref('')
const attachment = ref(null)
const errors = ref({})
const imagePreview = ref(null)
const editComment = ref(null)
const filePreview = ref(null)
const route = useRoute()

const { comments } = defineProps({
    comments: {
        type: Array,
        default: () => []
    }
})

const cleanHTML = DOMPurify.sanitize(text.value, {
    ALLOWED_TAGS: ['a', 'code', 'i', 'strong'],
    ALLOWED_ATTR: ['href', 'title']
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

    attachment.value = null // очищаємо новий файл

    if (comment.media && comment.media.length && comment.media[0].url) {
        const file = comment.media[0]
        if (file) {
            const extension = file.url.split('.').pop().toLowerCase()
            if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                imagePreview.value = file.url
                filePreview.value = null
            } else {
                imagePreview.value = null
                filePreview.value = {
                    url: file.url,
                    name: file.url.split('/').pop()
                }
            }
        }

        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            imagePreview.value = file.url
        } else {
            imagePreview.value = null
        }
        editComment.value.attachment_url = file.url
        editComment.value.attachment_type = file.type
    } else {
        imagePreview.value = null
        editComment.value.attachment_url = null
        editComment.value.attachment_type = null
    }


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
const validateForm = async () => {
    errors.value = {}

    // Ім'я
    if (!username.value.trim()) {
        errors.value.user_name = ['Ім’я обовʼязкове']
    } else if (!/^[a-zA-Z0-9]+$/.test(username.value)) {
        errors.value.user_name = ['Ім’я може містити лише латинські літери та цифри']
    }

    // Email
    if (!email.value.trim()) {
        errors.value.email = ['Email обовʼязковий']
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errors.value.email = ['Некоректний формат email']
    }

    // Домашня сторінка
    if (homepage.value && !/^https?:\/\/[^\s$.?#].[^\s]*$/.test(homepage.value)) {
        errors.value.homepage = ['Некоректний формат URL']
    }

    // Текст
    if (!text.value.trim()) {
        errors.value.content = ['Коментар обовʼязковий']
    } else {
        const cleanHTML = DOMPurify.sanitize(text.value, {
            ALLOWED_TAGS: ['a', 'code', 'i', 'strong'],
            ALLOWED_ATTR: ['href', 'title']
        })

        if (!cleanHTML.trim()) {
            errors.value.content = ['Коментар має містити лише дозволений вміст або текст']
        } else {
            text.value = cleanHTML
        }
    }

    // CAPTCHA
    if (!grecaptcha.getResponse()) {
        errors.value['g-recaptcha-response'] = ['Підтвердіть CAPTCHA']
    }

    // Файл
    if (attachment.value) {
        const file = attachment.value
        const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif']
        const isText = file.type === 'text/plain'

        if (!allowedImageTypes.includes(file.type) && !isText) {
            errors.value.attachment = ['Дозволено лише зображення (jpg, png, gif) або текстові файли (txt)']
        }

        if (isText && file.size > 100 * 1024) {
            errors.value.attachment = ['Максимальний розмір текстового файлу — 100KB']
        }

        if (allowedImageTypes.includes(file.type)) {
            const validImageSize = await checkImageSize(file)
            if (!validImageSize) {
                errors.value.attachment = ['Максимальний розмір зображення — 320x240 пікселів']
            }
        }
    }

    return Object.keys(errors.value).length === 0
}
const checkImageSize = (file) => {
    return new Promise((resolve) => {
        const img = new Image()
        const objectUrl = URL.createObjectURL(file)

        img.onload = () => {
            const isValid = img.width <= 320 && img.height <= 240
            URL.revokeObjectURL(objectUrl)
            resolve(isValid)
        }

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl)
            resolve(false)
        }

        img.src = objectUrl
    })
}

const validateField = (field) => {
    const val = {
        user_name: username.value.trim(),
        email: email.value.trim(),
        homepage: homepage.value.trim(),
        content: text.value.trim(),
        'g-recaptcha-response': grecaptcha.getResponse(),
    }

    const fieldErrors = {}

    if (field === 'user_name') {
        if (!val.user_name) fieldErrors.user_name = ['Ім’я є обовʼязковим']
        else if (!/^[a-zA-Z0-9]+$/.test(val.user_name)) fieldErrors.user_name = ['Лише латинські літери та цифри']
    }

    if (field === 'email') {
        if (!val.email) fieldErrors.email = ['Email є обовʼязковим']
        else if (!/^\S+@\S+\.\S+$/.test(val.email)) fieldErrors.email = ['Невірний формат email']
    }

    if (field === 'homepage' && val.homepage && !/^https?:\/\/[\w\-\.]+\.[a-z]{2,}/.test(val.homepage)) {
        fieldErrors.homepage = ['Невірний формат URL']
    }

    if (field === 'content') {
        if (!val.content) {
            fieldErrors.content = ['Текст коментаря є обовʼязковим']
        } else {
            const cleanHTML = DOMPurify.sanitize(val.content, {
                ALLOWED_TAGS: ['a', 'code', 'i', 'strong'],
                ALLOWED_ATTR: ['href', 'title']
            })

            if (!cleanHTML.trim()) {
                fieldErrors.content = ['Має бути текст або теги: <a>, <code>, <i>, <strong>']
            } else {
                text.value = cleanHTML
            }
        }
    }


    if (field === 'g-recaptcha-response') {
        if (!val['g-recaptcha-response']) fieldErrors['g-recaptcha-response'] = ['CAPTCHA обовʼязкова']
    }

    if (field === 'attachment' && attachment.value) {
        const file = attachment.value
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'text/plain']
        const maxSize = 100 * 1024 // 100KB

        if (!allowedTypes.includes(file.type)) {
            fieldErrors.attachment = ['Непідтримуваний тип файлу']
        } else if (file.type === 'text/plain' && file.size > maxSize) {
            fieldErrors.attachment = ['Текстовий файл повинен бути менше 100 КБ']
        }
    }

    errors.value = {
        ...errors.value,
        ...fieldErrors
    }

    // Очистити помилку, якщо поле стало валідним
    if (!fieldErrors[field]) delete errors.value[field]
}
watch(username, () => validateField('user_name'))
watch(email, () => validateField('email'))
watch(homepage, () => validateField('homepage'))
watch(text, () => validateField('content'))
watch(attachment, () => validateField('attachment'))
const submit = async () => {
    errors.value = {}
    const isValid = await validateForm()
    if (!isValid) {
        return // Не надсилаємо
    }
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

        reloadComments();

    } catch (e) {
        console.error('Помилка надсилання. Повна відповідь:', e)

        if (e.response?.data?.errors) {
            console.log('Validation errors:', e.response.data.errors);
            errors.value = e.response.data.errors;
        } else {
            alert('Помилка надсилання');
        }
    }
}


onMounted(() => {
    window.replyTo = replyTo
    window.editComment = openEditModal
    window.openCommentModal = openModal
})
const reloadComments = () => {
    const container = document.getElementById('commentsContainer');

    const params = new URLSearchParams({
        sort_by: 'created_at',
        sort_dir: 'desc'
    });

    axios.get(`/comments/listing?${params}`)
        .then(response => {
            container.innerHTML = response.data;
            if (window.lightbox) {
                lightbox.reload(); // або будь-яка ініціалізація
            }
        })
        .catch(err => {
            console.error('Не вдалося завантажити коментарі:', err);
        });
}

</script>

<template>
    <!--    <button class="btn btn-primary mb-3 create-comment" @click="openModal">📝 Залишити коментар</button>-->

    <!--    <h5 class="modal-title">-->
    <!--        {{ editComment ? 'Редагувати коментар' : (parent_id ? 'Відповідь на коментар' : 'Залишити коментар') }}-->
    <!--    </h5>-->

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
                        <div v-if="errors.user_name" class="text-danger mt-1">{{ errors.user_name[0] }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input v-model="email" type="email" class="form-control" />
                        <div v-if="errors.email" class="text-danger mt-1">{{ errors.email[0] }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Домашня сторінка</label>
                        <input v-model="homepage" type="url" class="form-control" />
                        <div v-if="errors.homepage" class="text-danger mt-1">{{ errors.homepage[0] }}</div>
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
                        <div v-if="errors.text" class="text-danger mt-1">{{ errors.text[0] }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="attachment" class="form-label">Прикріпити файл</label>
                        <input type="file" name="attachment" id="attachment" class="form-control" @change="handleFileChange" />
                        <div v-if="errors.attachment" class="text-danger mt-1">{{ errors.attachment[0] }}</div>
                        <!--                        <div id="image-preview" class="mt-2">-->
                        <!--                            <img v-if="imagePreview" :src="imagePreview" alt="Превʼю" class="img-fluid rounded" />-->
                        <!--                            <div v-else-if="editComment?.attachment_url">-->
                        <!--                                <a :href="editComment.attachment_url" target="_blank">📎 Переглянути прикріплений файл</a>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                        <div class="mb-2" v-if="imagePreview">
                            <a :href="imagePreview" target="_blank">
                                <img :src="imagePreview" class="img-thumbnail" width="100" />
                            </a>
                        </div>

                        <div class="mb-2" v-else-if="filePreview">
                            <a :href="filePreview.url" target="_blank">
                                {{ filePreview.name }}
                            </a>
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
