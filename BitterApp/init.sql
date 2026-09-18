-- Шаблон инициализации БД для проекта Bitter
-- Реальные пароли и пользователи задаются через переменные окружения / конфиг.

CREATE DATABASE IF NOT EXISTS Bitter_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Пример пользователя (пароль нужно заменить при развёртывании)
CREATE USER IF NOT EXISTS 'bitter_user'@'%' IDENTIFIED BY 'Password';
GRANT ALL PRIVILEGES ON Bitter_DB.* TO 'bitter_user'@'%';
FLUSH PRIVILEGES;
