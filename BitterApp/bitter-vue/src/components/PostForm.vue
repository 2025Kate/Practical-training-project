<template>
  <!-- Контейнер формы для создания поста -->
  <div class="text-form">
    <!-- 
      Текстовое поле для ввода содержимого поста
      v-model="postText" - двухстороннее связывание с переменной postText
      placeholder - подсказка для пользователя
      class="post-input" - CSS класс для стилизации
    -->
    <textarea 
      v-model="postText" 
      placeholder="Напишите что-нибудь..."
      class="post-input"
    ></textarea>
    
    <!-- 
      Кнопка для публикации поста
      @click="createPost" - обработчик клика, вызывает метод createPost
      class="submit-button" - CSS класс для стилизации
    -->
    <button @click="createPost" class="submit-button">Опубликовать</button>
  </div>
</template>

<script>
export default {
  // Локальное состояние компонента
  data() {
    return {
      // Переменная для хранения текста поста
      // Инициализируется пустой строкой
      postText: ''
    }
  },
  
  // Методы компонента
  methods: {
    /**
     * Метод для создания нового поста
     * Выполняет проверки и генерирует событие с данными поста
     */
    createPost() {
      // Проверка: текст не должен быть пустым или состоять только из пробелов
      if (this.postText.trim()) {
        // Генерация события 'create-post' с передачей данных:
        // - text: очищенный текст поста
        // - type: тип поста (в данном случае всегда 'my')
        this.$emit('create-post', {
          text: this.postText,
          type: 'my'
        });
        
        // Очистка текстового поля после успешной отправки
        this.postText = '';
      }
      // Если текст пустой - ничего не происходит (кнопка не работает)
    }
  }
}
</script>

<style scoped>
/* 
  Стили с атрибутом scoped применяются только к этому компоненту
  Это предотвращает конфликты стилей с другими компонентами
*/

/* Контейнер формы */
.text-form {
  display: flex; /* Используем flexbox для layout */
  flex-direction: column; /* Элементы располагаются вертикально */
  align-items: center; /* Центрирование по горизонтали */
  margin-top: 20px; /* Отступ сверху */
}

/* Стили для текстового поля */
.post-input {
  width: 100%; /* Ширина 100% родительского контейнера */
  height: 100px; /* Фиксированная высота */
  margin-bottom: 10px; /* Отступ снизу (между полем и кнопкой) */
  padding: 10px; /* Внутренние отступы */
  border-radius: 4px; /* Закругленные углы */
  border: 1px solid #ddd; /* Светло-серая рамка */
  
  /* Дополнительные возможности для улучшения UX: */
  resize: vertical; /* Разрешить изменение размера только по вертикали */
  font-family: inherit; /* Наследовать шрифт от родителя */
  font-size: 14px; /* Размер шрифта */
}

/* Стили для кнопки отправки */
.submit-button {
  background-color: #4CAF50; /* Зеленый цвет фона */
  color: white; /* Белый текст */
  padding: 10px 20px; /* Внутренние отступы: 10px сверху/снизу, 20px слева/справа */
  border: none; /* Без рамки */
  border-radius: 4px; /* Закругленные углы */
  cursor: pointer; /* Указатель при наведении */
  
  /* Дополнительные стили для улучшения UX: */
  transition: background-color 0.3s ease; /* Плавное изменение цвета */
  font-size: 14px; /* Размер шрифта */
  font-weight: bold; /* Жирный текст */
}

/* Эффект при наведении на кнопку */
.submit-button:hover {
  background-color: #45a049; /* Темно-зеленый цвет при наведении */
}

/* Эффект при нажатии на кнопку */
.submit-button:active {
  transform: scale(0.98); /* Легкое уменьшение при клике */
}

/* Эффект при фокусе (для accessibility) */
.submit-button:focus {
  outline: none; /* Убираем стандартный outline */
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.4); /* Кастомная тень вместо outline */
}

/* Стиль для disabled состояния (если понадобится в будущем) */
.submit-button:disabled {
  background-color: #cccccc; /* Серый цвет */
  cursor: not-allowed; /* Запрещающий курсор */
  opacity: 0.6; /* Полупрозрачность */
}
</style>