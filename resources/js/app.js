// import './bootstrap';
import { createApp } from 'vue';

import Login from './components/auth/Login.vue';
import MeterList from './components/MeterList.vue';
import MeterUpdate from './components/MeterUpdate.vue';
import Dashboard from './components/Dashboard.vue';


const app = createApp({});

app.component('login', Login);


app.component('dashboard', Dashboard);
app.component('meter-list', MeterList);
app.component('meter-update', MeterUpdate);

app.mount('#app');
// app.mount('#dashboard-app');





