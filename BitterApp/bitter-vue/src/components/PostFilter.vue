<template>
  <!-- Контейнер для кнопок фильтрации -->
  <div class="filter-buttons">
    <!-- 
      Кнопка фильтра "Мои посты"
      @click - обработчик клика, вызывает метод filterPosts с аргументом 'my'
      :class - динамически добавляет класс 'active' если currentFilter равен 'my'
    -->
    <button 
      @click="filterPosts('my')" 
      :class="{ active: currentFilter === 'my' }"
    >
      Мои посты
    </button>

    <!-- 
      Кнопка фильтра "Подписки"
      @click - вызывает filterPosts с аргументом 'subscribed'
      :class - активна если currentFilter равен 'subscribed'
    -->
    <button 
      @click="filterPosts('subscribed')" 
      :class="{ active: currentFilter === 'subscribed' }"
    >
      Посты пользователей, на которых я подписан
    </button>

    <!-- 
      Кнопка фильтра "Упоминания"
      @click - вызывает filterPosts с аргументом 'mentioned'
      :class - активна если currentFilter равен 'mentioned'
    -->
    <button 
      @click="filterPosts('mentioned')" 
      :class="{ active: currentFilter === 'mentioned' }"
    >
      Посты, где я упомянут
    </button>

    <!-- 
      Динамическая кнопка для фильтра по автору
      v-if - отображается только когда activeAuthorFilter не пустой
      @click - вызывает очистку фильтра автора
      :class - всегда активна (special-filter)
      × - символ закрытия для очистки фильтра
    -->
    <button 
      v-if="activeAuthorFilter"
      @click="clearAuthorFilter"
      :class="{ active: true }"
      class="special-filter"
    >
      Автор: {{ activeAuthorFilter }} ×
    </button>

    <!-- 
      Динамическая кнопка для фильтра по хэштегу
      v-if - отображается только когда activeHashtagFilter не пустой
      @click - вызывает очистку фильтра хэштега
      :class - всегда активна (special-filter)
      # - префикс хэштега, × - символ закрытия
    -->
    <button 
      v-if="activeHashtagFilter"
      @click="clearHashtagFilter"
      :class="{ active: true }"
      class="special-filter"
    >
      Хэштег: #{{ activeHashtagFilter }} ×
    </button>
  </div>
</template>

<script>
export default {
  // Получаемые свойства от родительского компонента
  props: {
    // Текущий активный фильтр (my, subscribed, mentioned)
    currentFilter: {
      type: String,
      default: 'my' // Значение по умолчанию
    },
    // Активный фильтр по автору (если установлен)
    activeAuthorFilter: {
      type: String,
      default: null // По умолчанию отсутствует
    },
    // Активный фильтр по хэштегу (если установлен)
    activeHashtagFilter: {
      type: String,
      default: null // По умолчанию отсутствует
    }
  },
  methods: {
    /**
     * Метод для обработки клика по кнопке фильтра
     * @param {string} type - тип фильтра ('my', 'subscribed', 'mentioned')
     * Генерирует событие 'filter-change' с переданным типом
     */
    filterPosts(type) {
      this.$emit('filter-change', type);
    },
    
    /**
     * Метод для очистки фильтра по автору
     * Генерирует событие 'clear-author-filter'
     * Родительский компонент должен обработать это событие
     */
    clearAuthorFilter() {
      this.$emit('clear-author-filter');
    },
    
    /**
     * Метод для очистки фильтра по хэштегу
     * Генерирует событие 'clear-hashtag-filter'
     * Родительский компонент должен обработать это событие
     */
    clearHashtagFilter() {
      this.$emit('clear-hashtag-filter');
    }
  }
}
</script>

<style scoped>
/* Контейнер для кнопок фильтрации */
.filter-buttons {
  display: flex; /* Горизонтальное расположение */
  justify-content: center; /* Центрирование по горизонтали */
  margin: 20px 0; /* Отступы сверху и снизу */
  gap: 10px; /* Расстояние между кнопками */
  flex-wrap: wrap; /* Перенос на новую строку при нехватке места */
}

/* Базовые стили для всех кнопок */
.filter-buttons button {
  padding: 10px 15px; /* Внутренние отступы */
  border: none; /* Без рамки */
  border-radius: 4px; /* Закругленные углы */
  background-color: #f0f0f0; /* Светло-серый фон */
  cursor: pointer; /* Указатель при наведении */
  transition: all 0.3s ease; /* Плавные анимации */
}

/* Стили для активной кнопки (обычные фильтры) */
.filter-buttons button.active {
  background-color: #4CAF50; /* Зеленый фон */
  color: black; /* Черный текст */
}

/* Стили для специальных кнопок (фильтры по автору/хэштегу) */
.filter-buttons button.special-filter {
  background-color: #4CAF50; /* Зеленый фон */
  color: black; /* Черный текст */
}

/* Стили при наведении на специальные кнопки */
.filter-buttons button.special-filter:hover {
  background-color: #4CAF50; /* Остается зеленым при наведении */
  /* Можно добавить эффекты: */
  /* transform: scale(1.05); - увеличение */
  /* box-shadow: 0 2px 5px rgba(0,0,0,0.2); - тень */
}
</style>