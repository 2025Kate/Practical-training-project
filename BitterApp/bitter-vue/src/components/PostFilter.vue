<template>
  <!-- Основной контейнер для кнопок фильтрации -->
  <div class="filter-buttons">
    
    <!-- Кнопка фильтра "Мои посты" -->
    <router-link 
      :to="{ name: 'filter', params: { filterType: 'my' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <!-- 
        navigate - функция Vue Router для перехода по маршруту
        isActive - показывает, активен ли сейчас этот маршрут
      -->
      <button 
        @click="navigate"
        :class="{ 
          // Кнопка активна если маршрут активен ИЛИ выбран фильтр 'my'
          active: isActive || currentFilter === 'my'
        }"
        aria-label="Показать мои посты"
      >
        Мои посты
      </button>
    </router-link>

    <!-- Кнопка фильтра "Подписки" -->
    <router-link 
      :to="{ name: 'filter', params: { filterType: 'subscribed' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <button 
        @click="navigate"
        :class="{ 
          active: isActive || currentFilter === 'subscribed'
        }"
        aria-label="Показать посты пользователей, на которых я подписан"
      >
        Посты пользователей, на которых я подписан
      </button>
    </router-link>

    <!-- Кнопка фильтра "Упоминания" -->
    <router-link 
      :to="{ name: 'filter', params: { filterType: 'mentioned' } }"
      custom
      v-slot="{ navigate, isActive }"
    >
      <button 
        @click="navigate"
        :class="{ 
          active: isActive || currentFilter === 'mentioned'
        }"
        aria-label="Показать посты, где я упомянут"
      >
        Посты, где я упомянут
      </button>
    </router-link>

    <!-- Динамические фильтры (появляются только когда активны) -->

    <!-- Фильтр по автору - показывается только если выбран автор -->
    <button 
      v-if="activeAuthorFilter"
      @click="removeAuthorFilter"
      :class="{ 
        active: isActiveAuthor
      }"
      class="special-filter"
      aria-label="Удалить фильтр по автору"
    >
      <!-- Показывает имя автора и кнопку удаления -->
      Автор: {{ activeAuthorFilter }} ×
    </button>

    <!-- Фильтр по хэштегу - показывается только если выбран хэштег -->
    <button 
      v-if="activeHashtagFilter"
      @click="removeHashtagFilter"
      :class="{ 
        active: isActiveHashtag
      }"
      class="special-filter"
      aria-label="Удалить фильтр по хэштегу"
    >
      <!-- Показывает хэштег и кнопку удаления -->
      Хэштег: #{{ activeHashtagFilter }} ×
    </button>
  </div>
</template>

<script>
/**
 * Компонент FilterButtons - панель фильтров для постов
 * Предоставляет интерфейс для переключения между различными фильтрами:
 * - Основные фильтры (мои посты, подписки, упоминания)
 * - Специальные фильтры (автор, хэштег) - показываются динамически
 * 
 * Использует Vue Router для управления состоянием через URL
 */
export default {

  name: 'FilterButtons',
  
  /**
   * Компонент получает данные от родительского компонента
   */
  props: {
    /**
     * Текущий активный основной фильтр
     * Используется для определения активной кнопки и возврата после удаления специальных фильтров
     */
    currentFilter: {
      type: String,  // Ожидаем строку
      default: 'my',  // Значение по умолчанию - фильтр "Мои посты"
      validator: value => ['my', 'subscribed', 'mentioned'].includes(value)  // Валидация допустимых значений
    },
    
    /**
     * Активный фильтр по автору
     * Если не null - показывает кнопку фильтра автора
     */
    activeAuthorFilter: {
      type: String, 
      default: null,
      required: false 
    },
    
    /**
     * Активный фильтр по хэштегу
     * Если не null - показывает кнопку фильтра хэштега
     */
    activeHashtagFilter: {
      type: String,  
      default: null,  
      required: false  
    }
  },
  
  /**
   * ВЫЧИСЛЯЕМЫЕ СВОЙСТВА (COMPUTED)
   * Автоматически обновляются при изменении зависимостей
   */
  computed: {
    /**
     * Проверяет, активен ли в данный момент маршрут фильтра по автору
     * Сравнивает параметры текущего маршрута с activeAuthorFilter
     * @returns {boolean} true если маршрут автора активен и соответствует фильтру
     */
    isActiveAuthor() {
      return this.$route.name === 'author' &&  // Проверяем имя маршрута
             this.$route.params.authorName === this.activeAuthorFilter;  // Проверяем совпадение имени автора
    },
    
    /**
     * Проверяет, активен ли в данный момент маршрут фильтра по хэштегу
     * Сравнивает параметры текущего маршрута с activeHashtagFilter
     * @returns {boolean} true если маршрут хэштега активен и соответствует фильтру
     */
    isActiveHashtag() {
      return this.$route.name === 'hashtag' &&  // Проверяем имя маршрута
             this.$route.params.tag === this.activeHashtagFilter;  // Проверяем совпадение хэштега
    }
  },
  
  /**
   * МЕТОДЫ КОМПОНЕНТА
   * Функции для обработки пользовательских действий
   */
  methods: {
    /**
     * Удаление фильтра по автору
     * Вызывает событие для родительского компонента и возвращает к основному фильтру
     */
    removeAuthorFilter() {
      // Генерируем кастомное событие для уведомления родительского компонента
      // Родитель должен обработать это событие и сбросить фильтр
      this.$emit('remove-author-filter');
      
      // Навигация обратно к основному фильтру через Vue Router
      // Это обеспечивает синхронизацию URL с состоянием фильтров
      this.$router.push({ 
        name: 'filter',  // Имя маршрута
        params: { 
          filterType: this.currentFilter  // Используем текущий основной фильтр
        } 
      });
    },
    
    /**
     * Удаление фильтра по хэштегу
     * Аналогично removeAuthorFilter но для хэштегов
     */
    removeHashtagFilter() {
      // Генерируем кастомное событие для уведомления родительского компонента
      this.$emit('remove-hashtag-filter');
      
      // Навигация обратно к основному фильтру
      this.$router.push({ 
        name: 'filter', 
        params: { 
          filterType: this.currentFilter 
        } 
      });
    }
  }
}
</script>


<style scoped>
/* 
  КОНТЕЙНЕР ДЛЯ КНОПОК ФИЛЬТРОВ
  Используем flexbox для гибкого расположения кнопок
*/
.filter-buttons {
  display: flex;  /* Flexbox layout */
  justify-content: center;  /* Центрируем кнопки по горизонтали */
  margin: 20px 0;  /* Вертикальные отступы: 20px сверху и снизу */
  gap: 10px;  /* Расстояние между кнопками (modern alternative to margin) */
  flex-wrap: wrap;  /* Разрешаем перенос на новую строку при нехватке места */
}


.filter-buttons button {
  padding: 10px 15px;  /* Внутренние отступы: 10px сверху/снизу, 15px слева/справа */
  border: none;  /* Убираем стандартную браузерную рамку */
  border-radius: 4px;  /* Закругляем углы на 4px */
  background-color: #f0f0f0;  /* Светло-серый фон для неактивных кнопок */
  cursor: pointer;  /* Курсор-указатель при наведении */
  transition: all 0.3s ease;  /* Плавные переходы для всех CSS свойств в течение 0.3s */
  text-decoration: none;  /* Убираем подчеркивание (для consistency) */
  color: inherit;  /* Наследуем цвет текста от родителя */
  font-family: inherit;  /* Наследуем шрифт */
  font-size: 14px;  /* Размер шрифта */
  min-height: 40px;  /* Минимальная высота для accessibility */
}

/* 
  СТИЛИ ДЛЯ АКТИВНОЙ КНОПКИ
  Применяются когда кнопка активна (класс active)
*/
.filter-buttons button.active {
  background-color: #4CAF50;  /* Зеленый фон для активного состояния */
  color: black;  /* Черный текст для лучшего контраста */
  font-weight: 500;  /* Полужирный текст */
  transform: translateY(-1px);  /* Легкое поднятие для 3D эффекта */
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);  /* Легкая тень для глубины */
}

/* 
  СТИЛИ ДЛЯ СПЕЦИАЛЬНЫХ ФИЛЬТРОВ (АВТОР, ХЭШТЕГ)
  Дополнительный класс для особого визуального оформления
*/
.filter-buttons button.special-filter {
  background-color: #4CAF50;  /* Зеленый фон как у активных кнопок */
  color: black;  /* Черный текст */
  font-style: italic;  /* Курсив для визуального отличия */
}

/* 
  ЭФФЕКТ ПРИ НАВЕДЕНИИ ДЛЯ СПЕЦИАЛЬНЫХ ФИЛЬТРОВ
  Изменение цвета при hover для интерактивности
*/
.filter-buttons button.special-filter:hover {
  background-color: #3e8e41;  /* Темно-зеленый фон при наведении */
  transform: translateY(-1px);  /* Легкое поднятие */
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);  /* Усиленная тень */
}

/* 
  ОБЩИЙ HOVER ЭФФЕКТ ДЛЯ ВСЕХ КНОПОК
  Улучшает пользовательский опыт
*/
.filter-buttons button:hover:not(.active) {
  background-color: #e0e0e0;  /* Немного темнее серый при наведении */
  transform: translateY(-1px);  /* Легкий подъем */
}

/* 
  АДАПТИВНОСТЬ ДЛЯ МОБИЛЬНЫХ УСТРОЙСТВ
  Медиа-запрос для экранов меньше 768px
*/
@media (max-width: 768px) {
  .filter-buttons {
    gap: 8px;  /* Уменьшаем расстояние между кнопками */
    margin: 15px 0;  /* Уменьшаем вертикальные отступы */
  }
  
  .filter-buttons button {
    padding: 8px 12px;  /* Уменьшаем отступы для компактности */
    font-size: 13px;  /* Уменьшаем размер шрифта */
    min-height: 36px;  /* Уменьшаем минимальную высоту */
  }
}
</style>