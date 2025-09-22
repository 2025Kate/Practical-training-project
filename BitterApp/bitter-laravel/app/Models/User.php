<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель User представляет сущность пользователя в системе.
 * Отвечает за взаимодействие с таблицей пользователей в базе данных
 * и определяет отношения с другими моделями.
 */
class User extends Model
{
    use HasFactory; // Трейт для использования фабрик в тестировании

    /**
     * Массив атрибутов, которые разрешены для массового заполнения.
     * Защищает от уязвимостей массового присвоения (Mass Assignment).
     * 
     * @var array<string>
     */
    protected $fillable = ['username', 'name', 'privacy'];

    /**
     * Определяет отношение "один ко многим" с моделью Post.
     * Пользователь может иметь множество постов.
     * Второй параметр 'author_id' указывает внешний ключ в таблице posts,
     * который связывает пост с автором.
     * 
     * @return HasMany Отношение с постами пользователя
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Определяет отношение "многие ко многим" для подписчиков пользователя.
     * Связывает пользователя с другими пользователями, которые на него подписаны.
     * 
     * - 'subscribe' - имя промежуточной таблицы
     * - 'user_id' - внешний ключ, указывающий на текущего пользователя (на кого подписываются)
     * - 'subscriber_id' - внешний ключ, указывающий на подписчика
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'subscribe', 'user_id', 'subscriber_id');
    }

    /**
     * Определяет отношение "многие ко многим" для подписок пользователя.
     * Связывает пользователя с другими пользователями, на которых он подписан.
     * 
     * - 'subscribe' - имя промежуточной таблицы
     * - 'subscriber_id' - внешний ключ, указывающий на текущего пользователя (кто подписывается)
     * - 'user_id' - внешний ключ, указывающий на пользователя, на которого подписан текущий
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriptions()
    {
        return $this->belongsToMany(User::class, 'subscribe', 'subscriber_id', 'user_id');
    }

    /**
     * Определяет отношение "многие ко многим" для упоминаний пользователя в постах.
     * Связывает пользователя с постами, в которых он упомянут.
     * 
     * - 'post_mention' - имя промежуточной таблицы для хранения упоминаний
     * - 'mentioned_user_id' - внешний ключ, указывающий на упомянутого пользователя
     * - 'post_id' - внешний ключ, указывающий на пост с упоминанием
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mentions()
    {
        return $this->belongsToMany(Post::class, 'post_mention', 'mentioned_user_id', 'post_id');
    }
}