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
import { useRoute, useRouter } from 'vue-router'
import PostForm from '@/components/PostForm.vue'
import PostFilter from '@/components/PostFilter.vue'
import PostList from '@/components/PostList.vue'

export default {
  components: { PostForm, PostFilter, PostList },
  
  setup() {
    const route = useRoute()
    const router = useRouter()

    // Состояние
    const posts = ref([])
    const authorPosts = ref([])
    const hashtagPosts = ref([])
    const currentFilter = ref('my')
    const activeAuthorFilter = ref(null)
    const activeHashtagFilter = ref(null)
    const isLoading = ref(false)
    const error = ref(null)

    const API_BASE = 'http://localhost:8000/api'

    // Функции для работы с API
    const fetchPosts = async () => {
      isLoading.value = true
      error.value = null
      
      try {
        const response = await fetch(`${API_BASE}/posts`)
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
        
        const result = await response.json()
        if (result.success) {
          posts.value = result.data.map(post => ({
            id: post.id,
            text: post.content,
            author: post.author ? post.author.username : 'Unknown',
            date: post.DateTime,
            author_id: post.author_id,
            hashtags: post.hashtags,
            mentions: post.mentions
          }))
        } else {
          error.value = result.message || 'Ошибка при загрузке постов'
        }
      } catch (err) {
        error.value = 'Не удалось подключиться к серверу: ' + err.message
        console.error('Error:', err)
      } finally {
        isLoading.value = false
      }
    }

    const fetchPostsByAuthor = async (username) => {
      isLoading.value = true
      error.value = null
      
      try {
        const response = await fetch(`${API_BASE}/posts/author/${encodeURIComponent(username)}`)
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
        
        const result = await response.json()
        if (result.success) {
          authorPosts.value = result.posts.map(post => ({
            id: post.id,
            text: post.content,
            author: post.author ? post.author.username : 'Unknown',
            date: post.DateTime,
            author_id: post.author_id,
            hashtags: post.hashtags,
            mentions: post.mentions
          }))
        } else {
          error.value = result.message || 'Ошибка при загрузке постов автора'
        }
      } catch (err) {
        error.value = 'Не удалось загрузить посты автора: ' + err.message
        console.error('Error:', err)
      } finally {
        isLoading.value = false
      }
    }

    const fetchPostsByHashtag = async (tag) => {
      isLoading.value = true
      error.value = null
      
      try {
        const response = await fetch(`${API_BASE}/posts/hashtag/${encodeURIComponent(tag)}`)
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
        
        const result = await response.json()
        if (result.success) {
          hashtagPosts.value = result.posts.map(post => ({
            id: post.id,
            text: post.content,
            author: post.author ? post.author.username : 'Unknown',
            date: post.DateTime,
            author_id: post.author_id,
            hashtags: post.hashtags,
            mentions: post.mentions
          }))
        } else {
          error.value = result.message || 'Ошибка при загрузке постов с хэштегом'
        }
      } catch (err) {
        error.value = 'Не удалось загрузить посты с хэштегом: ' + err.message
        console.error('Error:', err)
      } finally {
        isLoading.value = false
      }
    }

    const createPost = async (postData) => {
      isLoading.value = true
      error.value = null
      
      try {
        const response = await fetch(`${API_BASE}/posts`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ content: postData.text })
        })
        
        const result = await response.json()
        if (result.success) {
          const newPost = {
            id: result.data.id,
            text: result.data.content,
            author: result.data.author ? result.data.author.username : 'Unknown',
            date: result.data.DateTime,
            author_id: result.data.author_id,
            hashtags: result.data.hashtags,
            mentions: result.data.mentions
          }
          posts.value.unshift(newPost)
          return { success: true }
        } else {
          error.value = result.message || 'Ошибка при создании поста'
          return { success: false, errors: result.errors }
        }
      } catch (err) {
        error.value = 'Не удалось подключиться к серверу'
        console.error('Error creating post:', err)
        return { success: false }
      } finally {
        isLoading.value = false
      }
    }

    // Обработка параметров маршрута
    const handleRouteParams = () => {
      const { filterType, authorName, tag } = route.params
      
      activeAuthorFilter.value = null
      activeHashtagFilter.value = null
      
      if (filterType) {
        currentFilter.value = filterType
        fetchPosts()
      } else if (authorName) {
        activeAuthorFilter.value = authorName
        fetchPostsByAuthor(authorName)
      } else if (tag) {
        activeHashtagFilter.value = tag
        fetchPostsByHashtag(tag)
      } else {
        currentFilter.value = 'my'
        fetchPosts()
      }
    }

    // Вычисляемые свойства
    const filteredPosts = computed(() => {
      if (activeAuthorFilter.value) return authorPosts.value
      if (activeHashtagFilter.value) return hashtagPosts.value
      
      const currentUserId = 1
      const mockSubscriptions = [1, 5]
      
      return posts.value.filter(post => {
        if (currentFilter.value === 'my') {
          return post.author_id === currentUserId
        } else if (currentFilter.value === 'subscribed') {
          return mockSubscriptions.includes(post.author_id) && post.author_id !== currentUserId
        } else if (currentFilter.value === 'mentioned') {
          return post.mentions && post.mentions.some(mention => mention.id === currentUserId)
        }
        return true
      })
    })

    // Обработчики событий
    const handleCreatePost = async (postData) => {
      const result = await createPost(postData)
      if (result && !result.success) {
        console.error('Ошибка создания поста:', result.errors)
      }
    }

    const handleAuthorFilter = (authorName) => {
      router.push({ name: 'author', params: { authorName } })
    }

    const handleHashtagFilter = (tag) => {
      router.push({ name: 'hashtag', params: { tag } })
    }

    // Хуки
    onMounted(() => {
      handleRouteParams()
    })

    watch(route, () => {
      handleRouteParams()
    })

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