<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Hashtag;
use Illuminate\Support\Facades\DB;

/**
 * PostService - сервисный класс для работы с постами.
 * 
 * Инкапсулирует бизнес-логику работы с постами, включая:
 * - получение ленты новостей
 * - создание постов
 * - обработку упоминаний и хештегов
 * - фильтрацию по авторам и тегам
 */
class PostService
{
    /**
     * Получение ленты постов для текущего пользователя.
     * 
     * Включает посты:
     * 1. Созданные самим пользователем
     * 2. Созданные авторами, на которых подписан пользователь
     * 3. Посты, в которых пользователь упомянут
     * 
     * @param int $currentUserId ID текущего пользователя
     * @return \Illuminate\Database\Eloquent\Collection Коллекция постов с связанными данными
     */
    public function getFeedPosts(int $currentUserId)
    {
        return Post::with(['author', 'hashtags', 'mentions']) // Жадная загрузка связей
            ->where(function($query) use ($currentUserId) {
                // Посты автора
                $query->where('author_id', $currentUserId)
                    // Посты тех, на кого подписан
                    ->orWhereHas('author.subscribers', function($q) use ($currentUserId) {
                        $q->where('subscriber_id', $currentUserId);
                    })
                    // Посты где упомянут
                    ->orWhereHas('mentions', function($q) use ($currentUserId) {
                        $q->where('mentioned_user_id', $currentUserId);
                    });
            })
            ->latest('DateTime') // Сортировка по дате (новые сначала)
            ->limit(30) // Ограничение количества постов
            ->get();
    }

    /**
     * Создание нового поста.
     * 
     * Выполняется в транзакции для обеспечения целостности данных:
     * 1. Создание поста
     * 2. Обработка упоминаний пользователей
     * 3. Обработка хештегов
     * 
     * @param array $data Данные поста (содержимое)
     * @param int $currentUserId ID автора поста
     * @return Post Созданный пост с загруженными связями
     */
    public function createPost(array $data, int $currentUserId)
    {
        return DB::transaction(function () use ($data, $currentUserId) {
            // Создание основного поста
            $post = Post::create([
                'author_id' => $currentUserId,
                'content' => $data['content'],
                'DateTime' => now() // Текущая дата и время
            ]);

            // Обработка упоминаний пользователей в тексте
            $this->processMentions($post, $data['content']);
            
            // Обработка хештегов в тексте
            $this->processHashtags($post, $data['content']);

            // Возврат поста с загруженными связями
            return $post->load(['author', 'hashtags', 'mentions']);
        });
    }

    /**
     * Получение постов связанных с пользователем.
     * 
     * Включает:
     * 1. Посты, созданные пользователем
     * 2. Посты, в которых пользователь упомянут
     * 
     * @param User $user Пользователь
     * @return \Illuminate\Database\Eloquent\Collection Коллекция постов
     */
    public function getPostsByAuthor(User $user)
    {
        return Post::with(['author', 'hashtags', 'mentions'])
            ->where(function($query) use ($user) {
                $query->where('author_id', $user->id)
                    ->orWhereHas('mentions', function($q) use ($user) {
                        $q->where('mentioned_user_id', $user->id);
                    });
            })
            ->latest('DateTime')
            ->get();
    }

    /**
     * Получение постов по хештегу.
     * 
     * @param string $tag Имя хештега (без символа #)
     * @return \Illuminate\Database\Eloquent\Collection Коллекция постов с указанным хештегом
     */
    public function getPostsByHashtag(string $tag)
    {
        return Post::with(['author', 'hashtags', 'mentions'])
            ->whereHas('hashtags', function($query) use ($tag) {
                $query->where('name', $tag);
            })
            ->latest('DateTime')
            ->get();
    }

    /**
     * Обработка упоминаний пользователей в тексте поста.
     * 
     * Ищет упоминания вида @username в тексте и создает связи
     * между постом и упомянутыми пользователями.
     * 
     * @param Post $post Пост для обработки
     * @param string $content Текст поста
     * @return void
     */
    private function processMentions(Post $post, string $content): void
    {
        // Поиск всех упоминаний @username в тексте
        preg_match_all('/@([a-zA-Zа-яА-Я0-9_]+)/u', $content, $mentionMatches);
        
        if (!empty($mentionMatches[1])) {
            // Поиск пользователей по именам
            $mentionedUsers = User::whereIn('username', $mentionMatches[1])->get();
            
            // Создание связей многие-ко-многим
            $post->mentions()->attach($mentionedUsers->pluck('id'));
        }
    }

    /**
     * Обработка хештегов в тексте поста.
     * 
     * Ищет хештеги вида #tagname в тексте и создает связи
     * между постом и хештегами. Создает новые хештеги при необходимости.
     * 
     * @param Post $post Пост для обработки
     * @param string $content Текст поста
     * @return void
     */
    private function processHashtags(Post $post, string $content): void
    {
        // Поиск всех хештегов #tagname в тексте
        preg_match_all('/#([a-zA-Zа-яА-Я0-9_]+)/u', $content, $hashtagMatches);
        
        if (!empty($hashtagMatches[1])) {
            $hashtagIds = [];
            
            foreach ($hashtagMatches[1] as $tagName) {
                // Поиск или создание хештега
                $hashtag = Hashtag::firstOrCreate(['name' => $tagName]);
                $hashtagIds[] = $hashtag->id;
            }
            
            // Создание связей многие-ко-многим
            $post->hashtags()->attach($hashtagIds);
        }
    }
}