<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * RouteServiceProvider - сервис-провайдер для настройки маршрутов приложения.
 * 
 * Наследуется от базового ServiceProvider Laravel и отвечает за:
 * - определение привязок моделей к маршрутам
 * - настройку фильтров шаблонов маршрутов
 * - регистрацию групп маршрутов (web, api)
 * - настройку ограничения частоты запросов (rate limiting)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Путь к маршруту "home" для вашего приложения.
     * 
     * Обычно пользователи перенаправляются сюда после аутентификации.
     * Это константа, которая может быть использована в контроллерах аутентификации
     * и других компонентах приложения для единообразного указания целевого пути.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Загрузка конфигурации маршрутов приложения.
     * 
     * Выполняет настройку:
     * 1. Ограничения частоты запросов (rate limiting)
     * 2. Регистрацию групп маршрутов (web и api)
     * 3. Привязки моделей и фильтры шаблонов (если есть)
     * 
     * @return void
     */
    public function boot(): void
    {
        // Настройка ограничения частоты запросов
        $this->configureRateLimiting();

        // Регистрация маршрутов приложения
        $this->routes(function () {
            // API маршруты
            // Примечание: префикс 'api' не указывается здесь, так как он уже задан в routes/api.php
            Route::middleware('api') // Применение middleware группы 'api'
                ->namespace($this->namespace) // Пространство имен для контроллеров
                ->group(base_path('routes/api.php')); // Подключение файла с API маршрутами

            // Web маршруты
            Route::middleware('web') // Применение middleware группы 'web' (сессии, cookies, CSRF защита)
                ->namespace($this->namespace) // Пространство имен для контроллеров
                ->group(base_path('routes/web.php')); // Подключение файла с web маршрутами
        });
    }

    /**
     * Настройка ограничителей частоты запросов для приложения.
     * 
     * Rate limiting защищает приложение от DDoS атак и злоупотреблений,
     * ограничивая количество запросов от одного пользователя/IP за определенное время.
     * 
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        // Определение ограничителя для API с именем 'api'
        RateLimiter::for('api', function (Request $request) {
            // Ограничение: 60 запросов в минуту
            // Ключ ограничения: ID аутентифицированного пользователя или IP адрес
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}