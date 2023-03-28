import {createWebHistory, createRouter} from 'vue-router';
import Home from '../components/ExampleComponent.vue';

const routes = [
  {
    path: '/tes1',
    name: 'Home',
    component: Home,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;