<template>
  <!-- Контейнер для отдельного поста -->
  <div class="post">
    <!-- Заголовок с именем автора -->
    <h3>
      <!-- 
        Ссылка на автора с обработчиком клика
        @click.prevent - предотвращает переход по ссылке и вызывает фильтрацию по автору
        class="author-link" - стили для ссылки автора
      -->
      <a 
        href="#" 
        @click.prevent="filterByAuthor(post.author)"
        class="author-link"
      >
        {{ post.author }} <!-- Отображение имени автора -->
      </a>
    </h3>
    
    <!-- Содержимое поста с парсингом упоминаний и хэштегов -->
    <p class="post-content">
      <!-- 
        v-for для итерации по сегментам текста
        :key="index" - уникальный ключ для каждого сегмента (важно для производительности Vue)
      -->
      <template v-for="(segment, index) in processedText" :key="index">
        <!-- 
          Ссылка-упоминание (@username)
          v-if - отображается только для сегментов типа 'mention'
          @click.prevent - фильтрация по упомянутому пользователю
        -->
        <a
          v-if="segment.type === 'mention'"
          href="#"
          @click.prevent="filterByAuthor(segment.content)"
          class="mention-link"
        >
          @{{ segment.content }} <!-- Отображение упоминания с символом @ -->
        </a>
        
        <!-- 
          Ссылка-хэштег (#tag)
          v-else-if - отображается только для сегментов типа 'hashtag'
          @click.prevent - фильтрация по хэштегу
        -->
        <a
          v-else-if="segment.type === 'hashtag'"
          href="#"
          @click.prevent="filterByHashtag(segment.content)"
          class="hashtag-link"
        >
          #{{ segment.content }} <!-- Отображение хэштега с символом # -->
        </a>
        
        <!-- 
          Обычный текст (без форматирования)
          v-else - все остальные сегменты текста
        -->
        <span v-else class="plain-text">{{ segment.content }}</span>
      </template>
    </p>
    
    <!-- Дата поста в отформатированном виде -->
    <small class="post-date">{{ formatDate(post.date) }}</small>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  // Входные параметры компонента
  props: {
    post: {
      type: Object,
      required: true, // Обязательный параметр
      // Валидатор проверяет наличие обязательных полей в объекте поста
      validator: (post) => {
        return ['id', 'text', 'author', 'date'].every(
          key => key in post
        );
      }
    }
  },
  
  // Явное объявление генерируемых событий (лучшая практика)
  emits: ['filter-by-author', 'filter-by-hashtag'],
  
  // Composition API setup функция
  setup(props, { emit }) {
    /**
     * Форматирование даты для отображения
     * @param {string} dateString - строка с датой
     * @returns {string} отформатированная дата
     */
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleString();
    };

    /**
     * Вычисляемое свойство для парсинга текста поста
     * Разбивает текст на сегменты: обычный текст, упоминания, хэштеги
     */
    const processedText = computed(() => {
      const text = props.post.text;
      const segments = []; // Массив для хранения сегментов текста
      let lastIndex = 0;   // Индекс последнего обработанного символа
      
      // Регулярное выражение для поиска упоминаний (@username) и хэштегов (#tag)
      // (^|\s) - начало строки или пробел
      // ([@#]) - символ @ или #
      // ([a-zA-Zа-яА-Я0-9_]+) - буквы, цифры, подчеркивания (латиница и кириллица)
      const regex = /(^|\s)([@#])([a-zA-Zа-яА-Я0-9_]+)/g;
      
      let match;
      // Поиск всех совпадений с регулярным выражением
      while ((match = regex.exec(text)) !== null) {
        // Добавление обычного текста перед найденным упоминанием/хэштегом
        if (match.index > lastIndex) {
          segments.push({
            type: 'text',
            content: text.substring(lastIndex, match.index)
          });
        }
        
        // Добавление упоминания или хэштега
        segments.push({
          type: match[2] === '@' ? 'mention' : 'hashtag', // Определение типа
          content: match[3] // Извлеченное содержимое (без @/#)
        });
        
        // Обновление индекса последнего обработанного символа
        lastIndex = regex.lastIndex;
      }
      
      // Добавление оставшегося текста после последнего упоминания/хэштега
      if (lastIndex < text.length) {
        segments.push({
          type: 'text',
          content: text.substring(lastIndex)
        });
      }
      
      return segments;
    });

    /**
     * Обработчик клика по автору или упоминанию
     * @param {string} authorName - имя автора для фильтрации
     */
    const filterByAuthor = (authorName) => {
      emit('filter-by-author', authorName); // Генерация события
    };

    /**
     * Обработчик клика по хэштегу
     * @param {string} tag - хэштег для фильтрации
     */
    const filterByHashtag = (tag) => {
      emit('filter-by-hashtag', tag); // Генерация события
    };

    // Возврат методов и свойств для использования в template
    return {
      processedText,
      formatDate,
      filterByAuthor,
      filterByHashtag
    };
  }
}
</script>

<style scoped>
/* Стили контейнера поста */
.post {
  background-color: #f9f9f9; /* Светлый фон */
  padding: 15px; /* Внутренние отступы */
  margin-bottom: 20px; /* Отступ снизу между постами */
  border-radius: 8px; /* Закругленные углы */
  border-left: 4px solid #4CAF50; /* Зеленая левая граница как акцент */
  position: relative; /* Для позиционирования псевдо-элементов */
}

/* Стили содержимого поста */
.post-content {
  margin: 10px 0; /* Отступы сверху и снизу */
  line-height: 1.5; /* Межстрочный интервал */
  white-space: pre-wrap; /* Сохранение переносов и пробелов */
}

/* Стили даты поста */
.post-date {
  color: #666; /* Серый цвет */
  font-size: 0.9em; /* Уменьшенный размер шрифта */
}

/* Стили ссылки автора */
.author-link {
  color: #333; /* Темно-серый цвет */
  text-decoration: none; /* Без подчеркивания */
  font-weight: bold; /* Жирный шрифт */
}

/* Эффект при наведении на ссылку автора */
.author-link:hover {
  text-decoration: underline; /* Подчеркивание при наведении */
  color: #2a6496; /* Синий цвет при наведении */
}

/* Общие стили для ссылок упоминаний и хэштегов */
.mention-link, .hashtag-link {
  text-decoration: none; /* Без подчеркивания */
  position: relative; /* Для позиционирования псевдо-элементов */
  padding: 0 2px; /* Небольшие отступы */
  border-radius: 3px; /* Легкое закругление */
}

/* Специфические стили для упоминаний */
.mention-link {
  color: #1a73e8; /* Синий цвет */
}

/* Специфические стили для хэштегов */
.hashtag-link {
  color: #0d904f; /* Зеленый цвет */
}

/* Эффект при наведении на упоминания и хэштеги */
.mention-link:hover, .hashtag-link:hover {
  text-decoration: underline; /* Подчеркивание при наведении */
  background-color: transparent; /* Отключение фона при наведении */
}

/* 
  Псевдо-элементы для увеличения кликабельной области
  Создают невидимую область вокруг ссылки для удобства клика
*/
.mention-link::after, .hashtag-link::after {
  content: ''; /* Пустое содержимое */
  position: absolute; /* Абсолютное позиционирование */
  top: -4px; /* Расширение вверх */
  left: -4px; /* Расширение влево */
  right: -4px; /* Расширение вправо */
  bottom: -4px; /* Расширение вниз */
}

/* Стили для обычного текста */
.plain-text {
  white-space: pre-wrap; /* Сохранение переносов строк */
}
</style>