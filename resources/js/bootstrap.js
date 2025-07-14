import axios from 'axios';
import '@fortawesome/fontawesome-free/css/all.min.css';

window.axios = axios;

axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';




