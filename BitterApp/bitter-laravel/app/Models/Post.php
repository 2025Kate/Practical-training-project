<?php

// Объявление пространства имен для модели Post
namespace App\Models;

// Импорт необходимых классов Eloquent
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Объявление класса Post, который наследует Eloquent Model
class Post extends Model
{
    // Использование трейта HasFactory для поддержки фабрик
    use HasFactory;

    /**
     * Массив полей, разрешенных для массового присвоения (Mass Assignment)
     * Защита от уязвимости массового присвоения
     * 
     * author_id - ID автора поста (внешний ключ к users таблице)
     * content   - содержимое поста (текст)
     * DateTime  - дата и время создания поста (лучше использовать стандартное имя created_at)
     */
    protected $fillable = ['author_id', 'content', 'DateTime'];
    
    /**
     * Преобразование типов данных (Casting)
     * Автоматическое преобразование полей при получении из базы
     * 
     * 'DateTime' => 'datetime' - преобразует строку в объект Carbon
     * Это позволяет использовать: $post->DateTime->format('Y-m-d H:i:s')
     */
    protected $casts = [
        'DateTime' => 'datetime' // Преобразование в объект DateTime/Carbon
    ];

    /**
     * Отношение "многие к одному" (Many-to-One) с моделью User
     * Каждый пост принадлежит одному пользователю (автору)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        /**
         * Создание отношения belongsTo:
         * 1. User::class - связанная модель
         * 2. 'author_id' - внешний ключ в текущей таблице (posts)
         *    который ссылается на id в таблице users
         * 
         * По умолчанию Laravel ищет user_id, но у нас author_id
         */
        return $this->belongsTo(User::class, 'author_id');
        
        // Эквивалентно SQL: 
        // SELECT * FROM users WHERE id = {post.author_id}
    }

    /**
     * Отношение "многие ко многим" (Many-to-Many) с моделью User через упоминания
     * Пост может упоминать многих пользователей
     * Пользователь может быть упомянут во многих постах
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mentions()
    {
        /**
         * Создание отношения belongsToMany для упоминаний:
         * 1. User::class - модель упоминаемых пользователей
         * 2. 'post_mention' - имя промежуточной таблицы
         * 3. 'post_id' - FK для текущей модели (Post) в pivot таблице
         * 4. 'mentioned_user_id' - FK для связанной модели (User) в pivot таблице
         */
        return $this->belongsToMany(
            User::class,           // Модель упоминаемых пользователей
            'post_mention',        // Промежуточная таблица
            'post_id',             // FK для поста в pivot
            'mentioned_user_id'    // FK для упомянутого пользователя в pivot
        )->withTimestamps();       // Автоматическое управление временными метками в pivot
    }

    /**
     * Отношение "многие ко многим" (Many-to-Many) с моделью Hashtag
     * Пост может иметь много хэштегов
     * Хэштег может принадлежать многим постам
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hashtags()
    {
        /**
         * Создание отношения belongsToMany для хэштегов:
         * 1. Hashtag::class - модель хэштегов
         * 2. 'post_hashtag' - имя промежуточной таблицы
         * 3. 'post_id' - FK для текущей модели (Post) в pivot таблице
         * 4. 'hashtag_id' - FK для связанной модели (Hashtag) в pivot таблице
         */
        return $this->belongsToMany(
            Hashtag::class,    // Модель хэштегов
            'post_hashtag',    // Промежуточная таблица
            'post_id',         // FK для поста в pivot
            'hashtag_id'       // FK для хэштега в pivot
        )->withTimestamps();   // Автоматическое управление временными метками в pivot
    }
}

// Примеры использования модели Post:

// Создание поста
// $post = Post::create([
//     'author_id' => 1,
//     'content' => 'Hello world! #laravel',
//     'DateTime' => now()
// ]);

// Получение автора поста
// $author = $post->author;

// Добавление упоминаний
// $post->mentions()->attach([2, 3, 4]); // Упоминание пользователей с ID 2,3,4

// Добавление хэштегов
// $post->hashtags()->attach([1, 5]); // Добавление хэштегов с ID 1 и 5

// Получение всех постов с хэштегом laravel
// $laravelPosts = Post::whereHas('hashtags', function($query) {
//     $query->where('name', 'laravel');
// })->get();