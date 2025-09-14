// Импорт функции createStore из библиотеки Vuex для создания хранилища состояния
import { createStore } from 'vuex';

// Импорт модуля posts, который содержит состояние, мутации, действия и геттеры,
// связанные с функциональностью постов
import posts from './modules/posts';

// Импорт пользовательского плагина для сохранения состояния хранилища
// между сессиями (например, в localStorage)
import { persistStatePlugin } from './plugins/persistState';

// Создание и экспорт по умолчанию экземпляра хранилища Vuex
export default createStore({
  // Раздел modules: регистрация модулей хранилища
  // Модули позволяют разделить хранилище на более мелкие, управляемые части
  // Каждый модуль имеет собственное состояние, мутации, действия и геттеры
  modules: {
    posts // Регистрация модуля posts под пространством имен 'posts'
  },
  
  // Раздел plugins: регистрация плагинов Vuex
  plugins: [persistStatePlugin] // Регистрация плагина для сохранения состояния
});

// Структура созданного хранилища:
// store.state.posts - состояние модуля posts
// store.dispatch('posts/actionName') - вызов действия из модуля posts
// store.getters['posts/getterName'] - вызов геттера из модуля posts

// Пример использования в компонентах:
// import { useStore } from 'vuex';
// const store = useStore();
// store.dispatch('posts/fetchPosts'); - вызов действия fetchPosts из модуля posts
// const posts = computed(() => store.state.posts.posts); - доступ к состоянию posts