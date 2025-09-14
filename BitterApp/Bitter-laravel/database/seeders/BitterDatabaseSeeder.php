<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BitterDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Очищаем таблицы в правильном порядке (из-за foreign keys)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('post_hashtag')->truncate();
        DB::table('post_mention')->truncate();
        DB::table('hashtags')->truncate();
        DB::table('subscribe')->truncate();
        DB::table('posts')->truncate();
        DB::table('users')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Добавляем пользователей (как в исходном SQL)
        $users = [
            ['id' => 1, 'username' => 'Kate_2025', 'name' => 'Kate', 'privacy' => 0],
            ['id' => 2, 'username' => 'Max_star', 'name' => 'Max', 'privacy' => 0],
            ['id' => 3, 'username' => 'VasyaM', 'name' => 'Vasya', 'privacy' => 0],
            ['id' => 4, 'username' => 'Maria_flower', 'name' => 'Maria', 'privacy' => 0],
            ['id' => 5, 'username' => 'Master_Ivan', 'name' => 'Ivan', 'privacy' => 0],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'privacy' => $user['privacy'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Добавляем посты (как в исходном SQL)
        $posts = [
            ['id' => 1, 'author_id' => 3, 'content' => 'Привет! Это моё первое сообщение', 'DateTime' => '2025-05-05 13:15:04'],
            ['id' => 2, 'author_id' => 5, 'content' => 'Чиню утюги. Недорого!', 'DateTime' => '2025-05-07 12:05:35'],
            ['id' => 3, 'author_id' => 2, 'content' => 'Мои друзья: @Kate_2025, @Maria_flower, @VasyaM', 'DateTime' => '2025-05-09 18:53:20'],
            ['id' => 4, 'author_id' => 1, 'content' => '#ЛетоЭтоМаленькаяЖизнь Первый день лета прекрасен', 'DateTime' => '2025-06-01 22:30:07'],
            ['id' => 5, 'author_id' => 4, 'content' => '#ЛетоЭтоМаленькаяЖизнь #Отпуск #Море Сегодня улетаю в отпуск! Ждите новые советы :)', 'DateTime' => '2025-08-09 01:08:18'],
        ];

        foreach ($posts as $post) {
            DB::table('posts')->insert([
                'id' => $post['id'],
                'author_id' => $post['author_id'],
                'content' => $post['content'],
                'DateTime' => $post['DateTime'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Добавляем подписки (как в исходном SQL)
        $subscriptions = [
            ['user_id' => 1, 'subscriber_id' => 2],
            ['user_id' => 1, 'subscriber_id' => 4],
            ['user_id' => 1, 'subscriber_id' => 3],
            ['user_id' => 4, 'subscriber_id' => 2],
        ];

        foreach ($subscriptions as $subscription) {
            DB::table('subscribe')->insert([
                'user_id' => $subscription['user_id'],
                'subscriber_id' => $subscription['subscriber_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Добавляем хэштеги (как в исходном SQL)
        $hashtags = [
            ['id' => 1, 'name' => 'ЛетоЭтоМаленькаяЖизнь'],
            ['id' => 2, 'name' => 'Отпуск'],
            ['id' => 3, 'name' => 'Море'],
        ];

        foreach ($hashtags as $hashtag) {
            DB::table('hashtags')->insert([
                'id' => $hashtag['id'],
                'name' => $hashtag['name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Добавляем связи постов и хэштегов (как в исходном SQL)
        $postHashtags = [
            ['post_id' => 4, 'hashtag_id' => 1],
            ['post_id' => 5, 'hashtag_id' => 1],
            ['post_id' => 5, 'hashtag_id' => 2],
            ['post_id' => 5, 'hashtag_id' => 3],
        ];

        foreach ($postHashtags as $postHashtag) {
            DB::table('post_hashtag')->insert([
                'post_id' => $postHashtag['post_id'],
                'hashtag_id' => $postHashtag['hashtag_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Добавляем упоминания (как в исходном SQL)
        $mentions = [
            ['post_id' => 3, 'mentioned_user_id' => 1],
            ['post_id' => 3, 'mentioned_user_id' => 4],
            ['post_id' => 3, 'mentioned_user_id' => 3],
        ];

        foreach ($mentions as $mention) {
            DB::table('post_mention')->insert([
                'post_id' => $mention['post_id'],
                'mentioned_user_id' => $mention['mentioned_user_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('База данных успешно заполнена тестовыми данными!');
    }
}