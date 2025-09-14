<template>
  <!-- 
    КОРНЕВОЙ КОНТЕЙНЕР КОМПОНЕНТА HOME
    Основная страница приложения, содержащая все элементы ленты
  -->
  <div class="home">
    <!-- 
      КОМПОНЕНТ ФОРМЫ СОЗДАНИЯ ПОСТА
      @create-post - обработчик события создания нового поста
      Событие генерируется дочерним компонентом при отправке формы
    -->
    <PostForm @create-post="handleCreatePost" />
    
    <!-- 
      КОМПОНЕНТ ФИЛЬТРАЦИИ ПОСТОВ
      Передаем текущие состояния фильтров через props
      Слушаем события сброса специальных фильтров
    -->
    <PostFilter 
      :current-filter="currentFilter" 
      :active-author-filter="activeAuthorFilter"
      :active-hashtag-filter="activeHashtagFilter"
      @remove-author-filter="activeAuthorFilter = null"
      @remove-hashtag-filter="activeHashtagFilter = null"
    />
    
    <!-- 
      СОСТОЯНИЯ ЗАГРУЗКИ И ОШИБОК
      Условный рендеринг на основе состояния данных
    -->
    <div v-if="isLoading" class="loading">Загрузка...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <!-- 
      КОМПОНЕНТ СПИСКА ПОСТОВ
      Отображается только если нет загрузки и ошибок
      Передаем отфильтрованные посты и обработчики фильтрации
    -->
    <PostList 
      v-else 
      :posts="filteredPosts"
      @filter-by-author="handleAuthorFilter"
      @filter-by-hashtag="handleHashtagFilter"
    />
  </div>
</template>

<script>
// ИМПОРТ ФУНКЦИЙ COMPOSITION API
import { computed, onMounted, ref, watch } from 'vue'

// ИМПОРТ VUEX ДЛЯ УПРАВЛЕНИЯ СОСТОЯНИЕМ
import { useStore } from 'vuex'

// ИМПОРТ VUE ROUTER ДЛЯ РАБОТЫ С МАРШРУТИЗАЦИЕЙ
import { useRoute, useRouter } from 'vue-router'

// ИМПОРТ ДОЧЕРНИХ КОМПОНЕНТОВ
import PostForm from '@/components/PostForm.vue'
import PostFilter from '@/components/PostFilter.vue'
import PostList from '@/components/PostList.vue'

export default {
  // РЕГИСТРАЦИЯ ДОЧЕРНИХ КОМПОНЕНТОВ
  components: { PostForm, PostFilter, PostList },
  
  /**
   * ФУНКЦИЯ SETUP - ОСНОВНАЯ ФУНКЦИЯ COMPOSITION API
   * Вызывается до создания компонента
   * Здесь инициализируются все реактивные состояния и логика
   */
  setup() {
    // ПОЛУЧАЕМ ЭКЗЕМПЛЯРЫ STORE, ROUTE И ROUTER
    const store = useStore() // Для управления глобальным состоянием
    const route = useRoute() // Для доступа к текущему маршруту и параметрам
    const router = useRouter() // Для программной навигации

    // РЕАКТИВНЫЕ ПЕРЕМЕННЫЕ ДЛЯ СПЕЦИАЛЬНЫХ ФИЛЬТРОВ
    const activeAuthorFilter = ref(null) // Фильтр по автору (имя автора или null)
    const activeHashtagFilter = ref(null) // Фильтр по хэштегу (тег или null)

    /**
     * ХУК onMounted - ВЫПОЛНЯЕТСЯ ПОСЛЕ МОНТИРОВАНИЯ КОМПОНЕНТА В DOM
     * Идеальное место для загрузки данных и инициализации
     */
    onMounted(() => {
      // Загружаем посты при монтировании компонента
      store.dispatch('posts/fetchPosts')
      
      // Обрабатываем параметры маршрута при первоначальной загрузке
      handleRouteParams()
    })

    /**
     * WATCH ДЛЯ ОТСЛЕЖИВАНИЯ ИЗМЕНЕНИЙ МАРШРУТА
     * Срабатывает при любом изменении URL (навигации)
     */
    watch(route, (to) => {
      // При изменении маршрута обрабатываем новые параметры
      handleRouteParams()
    })

    /**
     * ФУНКЦИЯ ОБРАБОТКИ ПАРАМЕТРОВ МАРШРУТА
     * Анализирует параметры URL и соответствующим образом настраивает состояние
     */
    const handleRouteParams = () => {
      // ДЕСТРУКТУРИЗАЦИЯ ПАРАМЕТРОВ МАРШРУТА
      const { filterType, authorName, tag } = route.params
      
      // СБРАСЫВАЕМ СПЕЦИАЛЬНЫЕ ФИЛЬТРЫ ПЕРЕД ОБРАБОТКОЙ НОВЫХ ПАРАМЕТРОВ
      activeAuthorFilter.value = null
      activeHashtagFilter.value = null
      
      // ОБРАБОТКА РАЗЛИЧНЫХ ТИПОВ МАРШРУТОВ
      if (filterType) {
        // ОСНОВНОЙ ФИЛЬТР: my, subscribed, mentioned
        store.dispatch('posts/setFilter', filterType)
      } else if (authorName) {
        // ФИЛЬТР ПО АВТОРУ: /author/username
        activeAuthorFilter.value = authorName
        store.dispatch('posts/fetchPostsByAuthor', authorName)
      } else if (tag) {
        // ФИЛЬТР ПО ХЭШТЕГУ: /hashtag/vuejs
        activeHashtagFilter.value = tag
        store.dispatch('posts/fetchPostsByHashtag', tag)
      } else {
        // МАРШРУТ ПО УМОЛЧАНИЮ: / (корневой путь)
        store.dispatch('posts/setFilter', 'my')
      }
    }

    // ВЫЧИСЛЯЕМЫЕ СВОЙСТВА ДЛЯ ПОЛУЧЕНИЯ ДАННЫХ ИЗ VUEX STORE
    const currentFilter = computed(() => store.state.posts.currentFilter) // Текущий активный фильтр
    const allPosts = computed(() => store.state.posts.posts) // Все посты
    const authorPosts = computed(() => store.state.posts.authorPosts) // Посты конкретного автора
    const hashtagPosts = computed(() => store.state.posts.hashtagPosts) // Посты с конкретным хэштегом
    const isLoading = computed(() => store.getters['posts/isLoading']) // Состояние загрузки
    const error = computed(() => store.getters['posts/error']) // Сообщение об ошибке

    /**
     * ВЫЧИСЛЯЕМОЕ СВОЙСТВО ДЛЯ ФИЛЬТРАЦИИ ПОСТОВ
     * Автоматически пересчитывается при изменении зависимостей
     */
    const filteredPosts = computed(() => {
      // ЕСЛИ АКТИВЕН ФИЛЬТР ПО АВТОРУ - ВОЗВРАЩАЕМ ПОСТЫ АВТОРА
      if (activeAuthorFilter.value) {
        return authorPosts.value;
      }
      
      // ЕСЛИ АКТИВЕН ФИЛЬТР ПО ХЭШТЕГУ - ВОЗВРАЩАЕМ ПОСТЫ С ХЭШТЕГОМ
      if (activeHashtagFilter.value) {
        return hashtagPosts.value;
      }
      
      // МОК-ДАННЫЕ ДЛЯ ДЕМОНСТРАЦИИ (В РЕАЛЬНОМ ПРИЛОЖЕНИИ БРАТЬ ИЗ STORE/USER)
      const currentUserId = 1; // ID текущего пользователя
      const mockSubscriptions = [1, 5]; // ID авторов, на которых подписан пользователь
      
      // ФИЛЬТРАЦИЯ ПОСТОВ НА ОСНОВЕ ВЫБРАННОГО ФИЛЬТРА
      return allPosts.value.filter(post => {
        if (currentFilter.value === 'my') {
          // ПОКАЗЫВАТЬ ТОЛЬКО ПОСТЫ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ
          return post.author_id === currentUserId;
        } else if (currentFilter.value === 'subscribed') {
          // ПОКАЗЫВАТЬ ПОСТЫ АВТОРОВ, НА КОТОРЫХ ПОДПИСАН ПОЛЬЗОВАТЕЛЬ
          // Исключаем собственные посты (они уже в фильтре 'my')
          return mockSubscriptions.includes(post.author_id) && post.author_id !== currentUserId;
        } else if (currentFilter.value === 'mentioned') {
          // ПОКАЗЫВАТЬ ПОСТЫ, ГДЕ УПОМЯНУТ ПОЛЬЗОВАТЕЛЬ
          return post.mentions && post.mentions.some(mention => mention.id === currentUserId);
        }
        // ЕСЛИ ФИЛЬТР НЕ РАСПОЗНАН - ПОКАЗЫВАТЬ ВСЕ ПОСТЫ
        return true;
      });
    })

    /**
     * ОБРАБОТЧИК СОЗДАНИЯ НОВОГО ПОСТА
     * Вызывается при событии @create-post из компонента PostForm
     */
    const handleCreatePost = async (postData) => {
      // ДИСПАТЧИМ ДЕЙСТВИЕ СОЗДАНИЯ ПОСТА В VUEX STORE
      const result = await store.dispatch('posts/createPost', postData)
      
      // ОБРАБОТКА РЕЗУЛЬТАТА (ОШИБОК СОЗДАНИЯ)
      if (result && !result.success) {
        console.error('Ошибка создания поста:', result.errors)
        // В РЕАЛЬНОМ ПРИЛОЖЕНИИ: показать уведомление пользователю
      }
    }

    /**
     * ОБРАБОТЧИК ФИЛЬТРАЦИИ ПО АВТОРУ
     * Вызывается при клике на имя автора в компоненте PostList
     */
    const handleAuthorFilter = (authorName) => {
      // НАВИГАЦИЯ К МАРШРУТУ АВТОРА ЧЕРЕЗ VUE ROUTER
      // Это обеспечивает корректный URL и возможность использования кнопки "Назад"
      router.push({ name: 'author', params: { authorName } })
    }

    /**
     * ОБРАБОТЧИК ФИЛЬТРАЦИИ ПО ХЭШТЕГУ
     * Вызывается при клике на хэштег в компоненте PostList
     */
    const handleHashtagFilter = (tag) => {
      // НАВИГАЦИЯ К МАРШРУТУ ХЭШТЕГА
      router.push({ name: 'hashtag', params: { tag } })
    }

    // ВОЗВРАЩАЕМ ВСЕ СВОЙСТВА И МЕТОДЫ ДЛЯ ИСПОЛЬЗОВАНИЯ В TEMPLATE
    return { 
      currentFilter, 
      filteredPosts, 
      isLoading, 
      error, 
      activeAuthorFilter,
      activeHashtagFilter,
      handleCreatePost,
      handleAuthorFilter,
      handleHashtagFilter
    }
  }
}
</script>

<style scoped>
.home {
  max-width: 800px; /* Максимальная ширина контента */
  margin: 0 auto; /* Центрирование по горизонтали */
  padding: 20px; /* Внутренние отступы */
  font-family: Arial, sans-serif; /* Шрифт по умолчанию */
}

/* СТИЛЬ ДЛЯ СОСТОЯНИЯ ЗАГРУЗКИ */
.loading {
  text-align: center; /* Центрирование текста */
  padding: 20px; /* Отступы */
  color: #666; /* Цвет текста */
}

/* СТИЛЬ ДЛЯ СООБЩЕНИЙ ОБ ОШИБКАХ */
.error {
  text-align: center; /* Центрирование текста */
  padding: 20px; /* Отступы */
  color: #d32f2f; /* Красный цвет для ошибок */
  background-color: #ffebee; /* Светло-красный фон */
  border-radius: 4px; /* Закругленные углы */
  margin: 20px 0; /* Внешние отступы */
}
</style>