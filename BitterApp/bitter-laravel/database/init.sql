-- Создаем пользователя User с доступом с любого хоста
CREATE USER IF NOT EXISTS 'User'@'%' IDENTIFIED BY 'User_123';

-- Предоставляем все права на базу данных Bitter_DB
GRANT ALL PRIVILEGES ON Bitter_DB.* TO 'User'@'%';

-- Также предоставляем права пользователю root с любого хоста
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- Обновляем права
FLUSH PRIVILEGES;
