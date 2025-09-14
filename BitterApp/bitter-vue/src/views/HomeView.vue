<template>
  <div class="home">
    <PostForm @create-post="handleCreatePost" />
    
    <PostFilter 
      :current-filter="currentFilter" 
      :active-author-filter="activeAuthorFilter"
      :active-hashtag-filter="activeHashtagFilter"
      @remove-author-filter="activeAuthorFilter = null"
      @remove-hashtag-filter="activeHashtagFilter = null"
    />
    
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
import { computed, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import { useRoute, useRouter } from 'vue-router'
import PostForm from '@/components/PostForm.vue'
import PostFilter from '@/components/PostFilter.vue'
import PostList from '@/components/PostList.vue'

export default {
  components: { PostForm, PostFilter, PostList },
  
  setup() {
    const store = useStore()
    const route = useRoute()
    const router = useRouter()
    
    const activeAuthorFilter = ref(null)
    const activeHashtagFilter = ref(null)

    onMounted(() => {
      // Загружаем посты при монтировании
      store.dispatch('posts/fetchPosts')
      
      // Обрабатываем параметры маршрута при загрузке
      handleRouteParams()
    })

    // Отслеживаем изменения маршрута
    watch(route, (to) => {
      handleRouteParams()
    })

    const handleRouteParams = () => {
      const { filterType, authorName, tag } = route.params
      
      // Сбрасываем специальные фильтры
      activeAuthorFilter.value = null
      activeHashtagFilter.value = null
      
      if (filterType) {
        // Обработка основных фильтров
        store.dispatch('posts/setFilter', filterType)
      } else if (authorName) {
        // Обработка фильтра по автору
        activeAuthorFilter.value = authorName
        store.dispatch('posts/fetchPostsByAuthor', authorName)
      } else if (tag) {
        // Обработка фильтра по хэштегу
        activeHashtagFilter.value = tag
        store.dispatch('posts/fetchPostsByHashtag', tag)
      } else {
        // Маршрут по умолчанию
        store.dispatch('posts/setFilter', 'my')
      }
    }

    const currentFilter = computed(() => store.state.posts.currentFilter)
    const allPosts = computed(() => store.state.posts.posts)
    const authorPosts = computed(() => store.state.posts.authorPosts)
    const hashtagPosts = computed(() => store.state.posts.hashtagPosts)
    const isLoading = computed(() => store.getters['posts/isLoading'])
    const error = computed(() => store.getters['posts/error'])

    const filteredPosts = computed(() => {
      if (activeAuthorFilter.value) {
        return authorPosts.value;
      }
      
      if (activeHashtagFilter.value) {
        return hashtagPosts.value;
      }
      
      const currentUserId = 1;
      const mockSubscriptions = [1, 5];
      
      return allPosts.value.filter(post => {
        if (currentFilter.value === 'my') {
          return post.author_id === currentUserId;
        } else if (currentFilter.value === 'subscribed') {
          return mockSubscriptions.includes(post.author_id) && post.author_id !== currentUserId;
        } else if (currentFilter.value === 'mentioned') {
          return post.mentions && post.mentions.some(mention => mention.id === currentUserId);
        }
        return true;
      });
    })

    const handleCreatePost = async (postData) => {
      const result = await store.dispatch('posts/createPost', postData)
      if (result && !result.success) {
        console.error('Ошибка создания поста:', result.errors)
      }
    }

    const handleAuthorFilter = (authorName) => {
      // Навигация к маршруту автора вместо прямого вызова
      router.push({ name: 'author', params: { authorName } })
    }

    const handleHashtagFilter = (tag) => {
      // Навигация к маршруту хэштега вместо прямого вызова
      router.push({ name: 'hashtag', params: { tag } })
    }

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
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.error {
  text-align: center;
  padding: 20px;
  color: #d32f2f;
  background-color: #ffebee;
  border-radius: 4px;
  margin: 20px 0;
}
</style>