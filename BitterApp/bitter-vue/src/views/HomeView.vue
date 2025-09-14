<template>
  <!-- Основной контейнер компонента домашней страницы -->
  <div class="home">
    <!-- Компонент формы создания поста с обработчиком события create-post -->
    <PostForm @create-post="handleCreatePost" />
    
    <!-- Компонент фильтрации постов с передачей текущих состояний фильтров -->
    <PostFilter 
      :current-filter="currentFilter" 
      :active-author-filter="activeAuthorFilter"
      :active-hashtag-filter="activeHashtagFilter"
      @filter-change="handleFilterChange"
      @clear-author-filter="clearAuthorFilter"
      @clear-hashtag-filter="clearHashtagFilter"
    />
    
    <!-- Условный рендеринг: отображаем загрузку, ошибку или список постов -->
    <div v-if="isLoading" class="loading">Загрузка...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <PostList 
      v-else 
      :posts="filteredPosts"
      @filter-by-author="handleAuthorFilter"
      @filter-by-hashtag="handleHashtagFilter"
    />
  </div>
</template>

<script>
// Импорт функций Composition API из Vue
import { computed, onMounted, ref } from 'vue'
// Импорт функции для работы с хранилищем Vuex
import { useStore } from 'vuex'
// Импорт компонентов
import PostForm from '@/components/PostForm.vue'
import PostFilter from '@/components/PostFilter.vue'
import PostList from '@/components/PostList.vue'

export default {
  // Регистрация используемых компонентов
  components: { PostForm, PostFilter, PostList },
  
  // Функция setup() - основная функция Composition API
  setup() {
    // Получение экземпляра хранилища Vuex
    const store = useStore()
    
    // Реактивные ссылки для хранения активных фильтров
    const activeAuthorFilter = ref(null)  // Текущий фильтр по автору
    const activeHashtagFilter = ref(null) // Текущий фильтр по хэштегу

    // Хук жизненного цикла: выполняется после монтирования компонента
    onMounted(() => {
      // Диспатч действия для загрузки постов из хранилища
      store.dispatch('posts/fetchPosts')
    })

    // Вычисляемые свойства для получения данных из хранилища
    
    // Текущий основной фильтр (my/subscribed/mentioned)
    const currentFilter = computed(() => store.state.posts.currentFilter)
    // Все посты
    const allPosts = computed(() => store.state.posts.posts)
    // Посты отфильтрованные по автору
    const authorPosts = computed(() => store.state.posts.authorPosts)
    // Посты отфильтрованные по хэштегу
    const hashtagPosts = computed(() => store.state.posts.hashtagPosts)
    // Флаг загрузки данных
    const isLoading = computed(() => store.getters['posts/isLoading'])
    // Сообщение об ошибке (если есть)
    const error = computed(() => store.getters['posts/error'])

    // Вычисляемое свойство для отфильтрованных постов
    const filteredPosts = computed(() => {
      // Приоритет 1: если активен фильтр по автору
      if (activeAuthorFilter.value) {
        return authorPosts.value;
      }
      
      // Приоритет 2: если активен фильтр по хэштегу
      if (activeHashtagFilter.value) {
        return hashtagPosts.value;
      }
      
      // Приоритет 3: применение стандартных фильтров к основным постам
      
      // Моковые данные (в реальном приложении получались бы из хранилища)
      const currentUserId = 1; // ID текущего пользователя
      const mockSubscriptions = [1, 5]; // Моковые подписки пользователя
      
      // Фильтрация постов в зависимости от текущего фильтра
      return allPosts.value.filter(post => {
        // Фильтр "Мои посты" - только посты текущего пользователя
        if (currentFilter.value === 'my') {
          return post.author_id === currentUserId;
        } 
        // Фильтр "Подписки" - посты авторов, на которых подписан пользователь
        else if (currentFilter.value === 'subscribed') {
          return mockSubscriptions.includes(post.author_id) && post.author_id !== currentUserId;
        } 
        // Фильтр "Упоминания" - посты, где упомянут текущий пользователь
        else if (currentFilter.value === 'mentioned') {
          return post.mentions && post.mentions.some(mention => mention.id === currentUserId);
        }
        // Без фильтра - все посты
        return true;
      });
    })

    // Обработчик создания нового поста
    const handleCreatePost = async (postData) => {
      // Диспатч действия для создания поста
      const result = await store.dispatch('posts/createPost', postData)
      // Обработка ошибок (если действие возвращает информацию об ошибках)
      if (result && !result.success) {
        console.error('Ошибка создания поста:', result.errors)
      }
    }

    // Обработчик изменения основного фильтра
    const handleFilterChange = (filter) => {
      // Сброс специальных фильтров при изменении основного
      activeAuthorFilter.value = null
      activeHashtagFilter.value = null
      // Установка нового фильтра в хранилище
      store.dispatch('posts/setFilter', filter)
    }

    // Обработчик фильтрации по автору
    const handleAuthorFilter = (authorName) => {
      // Установка активного фильтра по автору
      activeAuthorFilter.value = authorName
      // Сброс фильтра по хэштегу
      activeHashtagFilter.value = null
      // Загрузка постов автора
      store.dispatch('posts/fetchPostsByAuthor', authorName)
    }

    // Обработчик фильтрации по хэштегу
    const handleHashtagFilter = (tag) => {
      // Установка активного фильтра по хэштегу
      activeHashtagFilter.value = tag
      // Сброс фильтра по автору
      activeAuthorFilter.value = null
      // Загрузка постов с хэштегом
      store.dispatch('posts/fetchPostsByHashtag', tag)
    }

    // Очистка фильтра по автору
    const clearAuthorFilter = () => {
      activeAuthorFilter.value = null
      // Возврат к стандартному фильтру
      store.dispatch('posts/setFilter', 'my')
    }

    // Очистка фильтра по хэштегу
    const clearHashtagFilter = () => {
      activeHashtagFilter.value = null
      // Возврат к стандартному фильтру
      store.dispatch('posts/setFilter', 'my')
    }

    // Возврат всех свойств и методов для использования в шаблоне
    return { 
      currentFilter, 
      filteredPosts, 
      isLoading, 
      error, 
      activeAuthorFilter,
      activeHashtagFilter,
      handleCreatePost, 
      handleFilterChange,
      handleAuthorFilter,
      handleHashtagFilter,
      clearAuthorFilter,
      clearHashtagFilter
    }
  }
}
</script>

<style scoped>
/* Стили компонента с областью видимости scoped */

.home {
  max-width: 800px;        /* Максимальная ширина контейнера */
  margin: 0 auto;          /* Центрирование по горизонтали */
  padding: 20px;           /* Внутренние отступы */
  font-family: Arial, sans-serif; /* Шрифт по умолчанию */
}

.loading {
  text-align: center;      /* Центрирование текста */
  padding: 20px;           /* Внутренние отступы */
  color: #666;             /* Цвет текста - серый */
}

.error {
  text-align: center;      /* Центрирование текста */
  padding: 20px;           /* Внутренние отступы */
  color: #d32f2f;          /* Цвет текста - красный */
  background-color: #ffebee; /* Фоновый цвет - светлый красный */
  border-radius: 4px;      /* Скругление углов */
  margin: 20px 0;          /* Внешние отступы */
}
</style>