<?php

// Объявление пространства имен для контроллера
namespace App\Http\Controllers;

// Импорт необходимых классов
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\PostService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// Объявление класса PostController, который наследует базовый Controller
class PostController extends Controller
{
    // Защищенные свойства для хранения экземпляров сервисов
    protected $postService;
    protected $userService;

    // Конструктор класса с внедрением зависимостей (Dependency Injection)
    public function __construct(PostService $postService, UserService $userService)
    {
        // Инициализация сервиса работы с постами
        $this->postService = $postService;
        // Инициализация сервиса работы с пользователями
        $this->userService = $userService;
    }

    /**
     * Метод для получения ленты постов
     * GET /api/posts
     */
    public function index(Request $request): JsonResponse
    {
        // Временное жесткое указание ID текущего пользователя
        // В реальном приложении получается из аутентификации
        $currentUserId = 1;
        
        // Получение постов для ленты через сервис
        $posts = $this->postService->getFeedPosts($currentUserId);

        // Возврат JSON ответа с успешным результатом
        return response()->json([
            'success' => true,      // Флаг успешного выполнения
            'data' => $posts,       // Данные с постами
            'message' => 'Посты успешно загружены', // Сообщение для клиента
            'count' => $posts->count() // Количество возвращенных постов
        ]);
    }

    /**
     * Метод для создания нового поста
     * POST /api/posts
     */
    public function store(Request $request): JsonResponse
    {
        // Валидация входных данных запроса
        $validated = $request->validate([
            'content' => 'required|string|max:280' // Контент обязателен, строка, макс. 280 символов
        ]);

        // Временное жесткое указание ID текущего пользователя
        $currentUserId = 1;
        
        // Создание поста через сервис с передачей валидированных данных и ID пользователя
        $post = $this->postService->createPost($validated, $currentUserId);

        // Возврат JSON ответа с созданным постом и статусом 201 (Created)
        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Пост успешно создан'
        ], 201); // HTTP статус 201 - Created
    }

    /**
     * Метод для получения постов по автору (username)
     * GET /api/posts/author/{username}
     */
    public function byAuthor($username): JsonResponse
    {
        // Логирование запроса для отладки
        Log::info('Searching for author:', ['username' => $username]);
        
        // Поиск пользователя по username через сервис
        $user = $this->userService->findByUsername($username);
        
        // Проверка найден ли пользователь
        if (!$user) {
            // Возврат ошибки 404 если пользователь не найден
            return response()->json([
                'success' => false,
                'message' => 'Автор не найден'
            ], 404); // HTTP статус 404 - Not Found
        }

        // Логирование найденного пользователя
        Log::info('User found:', ['user_id' => $user->id, 'username' => $user->username]);

        // Получение постов автора через сервис
        $posts = $this->postService->getPostsByAuthor($user);

        // Логирование количества найденных постов
        Log::info('Posts found:', ['count' => $posts->count()]);

        // Возврат JSON ответа с данными автора и его постами
        return response()->json([
            'success' => true,
            'user' => $user,        // Информация об авторе
            'posts' => $posts,      // Посты автора
            'message' => 'Данные автора'
        ]);
    }

    /**
     * Метод для получения постов по хэштегу
     * GET /api/posts/hashtag/{tag}
     */
    public function byHashtag($tag): JsonResponse
    {
        // Получение постов по хэштегу через сервис
        $posts = $this->postService->getPostsByHashtag($tag);

        // Возврат JSON ответа с данными по хэштегу
        return response()->json([
            'success' => true,
            'hashtag' => $tag,      // Запрошенный хэштег
            'posts' => $posts,      // Посты с этим хэштегом
            'message' => 'Данные хэштега'
        ]);
    }

    /**
     * Метод для тестирования подключения к базе данных
     * GET /api/posts/test-connection
     */
    public function testConnection(): JsonResponse 
    {
        try {
            // Попытка подключения к базе данных
            DB::connection()->getPdo();
            
            // Возврат успешного ответа если подключение установлено
            return response()->json([
                'success' => true,
                'message' => 'Database connection successful'
            ]);
        } catch (\Exception $e) {
            // Возврат ошибки если подключение не удалось
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 500); // HTTP статус 500 - Internal Server Error
        }
    }
}