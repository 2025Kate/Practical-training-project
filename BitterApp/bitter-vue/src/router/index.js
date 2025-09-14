// Импорт необходимых функций из Vue Router
// createRouter - функция для создания маршрутизатора
// createWebHistory - функция для создания истории браузера на основе HTML5 History API
import { createRouter, createWebHistory } from 'vue-router';

// Импорт компонента главной страницы
// @ - алиас для папки src, настроенный в Vite/vue-cli
import HomeView from '@/views/HomeView.vue';

/**
 * МАССИВ МАРШРУТОВ (ROUTES)
 * Определяет все возможные пути в приложении и их соответствие компонентам
 * Каждый маршрут - объект с конфигурацией
 */
const routes = [
  {
    path: '/', // Корневой путь URL
    name: 'home', // Уникальное имя маршрута для программной навигации
    component: HomeView, // Компонент, который будет отображаться для этого пути
    // Метаданные, props и другие опции могут быть добавлены здесь
  },
  {
    path: '/filter/:filterType', // Динамический путь с параметром :filterType
    name: 'filter', // Имя маршрута для использования в router-link и программной навигации
    component: HomeView, // Используем тот же компонент HomeView но с разными параметрами
    props: true // Включение передачи параметров маршрута как props в компонент
    // Когда true, параметр :filterType будет передан в HomeView как prop с именем filterType
  },
  {
    path: '/author/:authorName', // Динамический путь с параметром имени автора
    name: 'author', // Уникальное имя для маршрута автора
    component: HomeView, // Тот же компонент HomeView
    props: true // Параметр :authorName будет передан как prop authorName
    // Это позволяет компоненту HomeView реагировать на изменения автора
  },
  {
    path: '/hashtag/:tag', // Динамический путь с параметром хэштега
    name: 'hashtag', // Уникальное имя для маршрута хэштега
    component: HomeView, // Снова используем HomeView
    props: true // Параметр :tag будет передан как prop tag
    // Компонент может использовать этот prop для фильтрации по хэштегу
  }
];

/**
 * СОЗДАНИЕ ЭКЗЕМПЛЯРА ROUTER
 * createRouter создает и настраивает экземпляр маршрутизатора
 */
const router = createRouter({
  /**
   * НАСТРОЙКА ИСТОРИИ (HISTORY)
   * createWebHistory создает историю на основе HTML5 History API
   * Это позволяет использовать "красивые" URL без хэша (#)
   * 
   * import.meta.env.BASE_URL - базовый URL приложения из конфигурации Vite
   * Полезно для деплоя в подпапку (например, на GitHub Pages)
   */
  history: createWebHistory(import.meta.env.BASE_URL),
  
  /**
   * ПЕРЕДАЧА МАССИВА МАРШРУТОВ
   * routes: routes - определение всех маршрутов приложения
   * Можно использовать сокращенную запись (ES6 enhanced object literals)
   */
  routes, // Эквивалентно routes: routes
  
  /**
   * ДОПОЛНИТЕЛЬНЫЕ ОПЦИИ (можно добавить при необходимости):
   * 
   * scrollBehavior(to, from, savedPosition) {
   *   // Контроль поведения прокрутки при навигации
   *   return { top: 0 } // Прокрутка к верху страницы
   * },
   * 
   * linkActiveClass: 'router-link-active', // Класс для активных ссылок
   * linkExactActiveClass: 'router-link-exact-active', // Класс для точно активных ссылок
   */
});

// Экспорт созданного маршрутизатора для использования в основном приложении
export default router;