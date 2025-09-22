<?php

// Объявление пространства имен для модели (стандартное расположение в Laravel)
namespace App\Models;

// Импорт необходимых классов
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Объявление класса Hashtag, который наследует Eloquent Model
class Hashtag extends Model
{
    // Использование трейта HasFactory для поддержки фабрик и сидеров
    use HasFactory;

    /**
     * Массив полей, которые разрешено массово присваивать (mass assignment)
     * Защита от уязвимости массового присвоения (Mass Assignment Vulnerability)
     * Только поле 'name' можно установить через create() или update()
     */
    protected $fillable = ['name'];

    // protected $guarded = []; // Альтернативный подход - разрешить все поля кроме указанных

    /**
     * Определение отношения "многие ко многим" с моделью Post
     * 
     * Отношение означает:
     - Один хэштег может принадлежать многим постам
     - Один пост может иметь много хэштегов
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        /**
         * Создание отношения belongsToMany с указанием:
         * 1. Post::class - связанная модель
         * 2. 'post_hashtag' - имя промежуточной (pivot) таблицы
         * 3. 'hashtag_id' - foreign key для текущей модели (Hashtag) в pivot таблице
         * 4. 'post_id' - foreign key для связанной модели (Post) в pivot таблице
         * 
         * ->withTimestamps() - автоматическое обновление created_at и updated_at
         * в промежуточной таблице при добавлении/удалении связей
         */
        return $this->belongsToMany(
            Post::class,     // Связанная модель
            'post_hashtag',  // Имя промежуточной таблицы
            'hashtag_id',    // FK(внешний ключ) для текущей модели в pivot(промежуточная) таблице
            'post_id'        // FK для связанной модели в pivot таблице
        )->withTimestamps(); // Включение временных меток в pivot таблице
    }
}

// Пример использования модели Hashtag:

// Создание хэштега
// $hashtag = Hashtag::create(['name' => 'laravel']);

// Получение всех постов с этим хэштегом
// $posts = $hashtag->posts;

// Добавление хэштега к посту
// $hashtag->posts()->attach($postId);

// Синхронизация хэштегов поста
// $hashtagIds = [1, 2, 3];
// $hashtag->posts()->sync($hashtagIds);