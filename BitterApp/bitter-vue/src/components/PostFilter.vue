<template>
  <div class="filter-buttons">
    <!-- Основные кнопки фильтров -->
    <router-link 
      :to="{ name: 'filter', params: { filterType: 'my' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <button 
        @click="navigate"
        :class="{ active: isActive || currentFilter === 'my' }"
      >
        Мои посты
      </button>
    </router-link>

    <router-link 
      :to="{ name: 'filter', params: { filterType: 'subscribed' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <button 
        @click="navigate"
        :class="{ active: isActive || currentFilter === 'subscribed' }"
      >
        Посты пользователей, на которых я подписан
      </button>
    </router-link>

    <router-link 
      :to="{ name: 'filter', params: { filterType: 'mentioned' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <button 
        @click="navigate"
        :class="{ active: isActive || currentFilter === 'mentioned' }"
      >
        Посты, где я упомянут
      </button>
    </router-link>

    <!-- Специальные фильтры -->
    <button 
      v-if="activeAuthorFilter"
      @click="removeAuthorFilter"
      :class="{ active: isActiveAuthor }"
      class="special-filter"
    >
      Автор: {{ activeAuthorFilter }} ×
    </button>

    <button 
      v-if="activeHashtagFilter"
      @click="removeHashtagFilter"
      :class="{ active: isActiveHashtag }"
      class="special-filter"
    >
      Хэштег: #{{ activeHashtagFilter }} ×
    </button>
  </div>
</template>

<script>
export default {
  props: {
    currentFilter: {
      type: String,
      default: 'my'
    },
    activeAuthorFilter: {
      type: String,
      default: null
    },
    activeHashtagFilter: {
      type: String,
      default: null
    }
  },
  computed: {
    isActiveAuthor() {
      return this.$route.name === 'author' && this.$route.params.authorName === this.activeAuthorFilter;
    },
    isActiveHashtag() {
      return this.$route.name === 'hashtag' && this.$route.params.tag === this.activeHashtagFilter;
    }
  },
  methods: {
    removeAuthorFilter() {
      this.$emit('remove-author-filter');
      // Возвращаемся к основному фильтру
      this.$router.push({ name: 'filter', params: { filterType: this.currentFilter } });
    },
    removeHashtagFilter() {
      this.$emit('remove-hashtag-filter');
      // Возвращаемся к основному фильтру
      this.$router.push({ name: 'filter', params: { filterType: this.currentFilter } });
    }
  }
}
</script>

<style scoped>
.filter-buttons {
  display: flex;
  justify-content: center;
  margin: 20px 0;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-buttons button {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  background-color: #f0f0f0;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  color: inherit;
}

.filter-buttons button.active {
  background-color: #4CAF50;
  color: black;
}

.filter-buttons button.special-filter {
  background-color: #4CAF50;
  color: black;
}

.filter-buttons button.special-filter:hover {
  background-color: #3e8e41;
}
</style>