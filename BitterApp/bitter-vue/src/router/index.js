import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '@/views/HomeView.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/filter/:filterType',
    name: 'filter',
    component: HomeView,
    props: true
  },
  {
    path: '/author/:authorName',
    name: 'author',
    component: HomeView,
    props: true
  },
  {
    path: '/hashtag/:tag',
    name: 'hashtag',
    component: HomeView,
    props: true
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;