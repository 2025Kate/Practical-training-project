FROM node:22-alpine

WORKDIR /app

# Копирование package.json и package-lock.json
COPY package*.json ./

# Установка зависимостей
RUN npm install

# Копирование исходного кода
COPY . .

# Сборка приложения для production
RUN npm run build

# Экспорт порта
EXPOSE 5173

# Запуск сервера разработки
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
