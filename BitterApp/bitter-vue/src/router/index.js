// Импорт необходимых функций из Vue Router
// createRouter - функция для создания экземпляра маршрутизатора
// createWebHistory - функция для создания истории на основе HTML5 History API
import { createRouter, createWebHistory } from 'vue-router';

// Импорт компонента для главной страницы
import HomeView from '@/views/HomeView.vue';

// Определение массива маршрутов (routes)
// Каждый маршрут - объект с конфигурацией
const routes = [
  {
    path: '/',           // URL путь для маршрута
    name: 'home',        // Уникальное имя маршрута (используется для программной навигации)
    component: HomeView, // Компонент, который будет отображаться по этому пути
  }
];

// Создание экземпляра маршрутизатора
const router = createRouter({
  // Настройка режима истории
  // createWebHistory - использует HTML5 History API (чистые URL без #)
  // import.meta.env.BASE_URL - базовый URL приложения (из конфигурации Vite)
  history: createWebHistory(import.meta.env.BASE_URL),
  
  // Передача массива маршрутов
  routes,
});

// Экспорт маршрутизатора для использования в основном приложении
export default router;