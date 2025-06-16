import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import {createApp} from 'vue';
import axios from 'axios';

import CommentForm from './components/CommentForm.vue'
import Comment from './components/AnswerForm.vue'

const app = createApp(CommentForm)
app.mount('#vue-app')
