export default {
    // Включение пространства имен для модуля
    // Позволяет изолировать состояние/мутации/действия/геттеры этого модуля
    // Доступ: store.state.posts.posts (вместо store.state.posts)
    namespaced: true,
    
    // Начальное состояние модуля
    state: {
        posts: [],               // Основной массив всех постов
        currentFilter: 'my',     // Текущий активный фильтр (my, subscribed, mentioned)
        loading: false,          // Флаг загрузки данных
        error: null,             // Сообщение об ошибке (если есть)
        hashtagPosts: [],        // Посты, отфильтрованные по хэштегу (отдельное хранилище)
        authorPosts: [],         // Посты, отфильтрованные по автору (отдельное хранилище)
        currentRoute: null       // Текущий маршрут для восстановления состояния
    },
    
    // Мутации - синхронные функции для изменения состояния
    // Мутации должны быть простыми и предсказуемыми
    mutations: {
        // Установка массива постов
        SET_POSTS(state, posts) {
            state.posts = posts;
        },
        
        // Добавление нового поста в начало массива
        ADD_POST(state, post) {
            state.posts.unshift(post); // unshift добавляет в начало массива
        },
        
        // Установка текущего фильтра
        SET_FILTER(state, filter) {
            state.currentFilter = filter;
        },
        
        // Установка флага загрузки
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        
        // Установка ошибки
        SET_ERROR(state, error) {
            state.error = error;
        },
        
        // Очистка ошибки
        CLEAR_ERROR(state) {
            state.error = null;
        },
        
        // Установка постов по хэштегу
        SET_HASHTAG_POSTS(state, posts) {
            state.hashtagPosts = posts;
        },
        
        // Установка постов по автору
        SET_AUTHOR_POSTS(state, posts) {
            state.authorPosts = posts;
        },
        
        // Сохранение текущего маршрута
        SET_CURRENT_ROUTE(state, route) {
            state.currentRoute = route;
        },
        
        // Сброс специальных фильтров
        RESET_SPECIAL_FILTERS(state) {
            state.activeAuthorFilter = null; 
            state.activeHashtagFilter = null;
        }
    },
    
    // Действия - асинхронные операции для работы с API
    // Могут вызывать мутации и другие действия
    actions: {
        // Загрузка основной ленты постов - асинхронное действие
        async fetchPosts({ commit }) {
            // Устанавливаем флаг загрузки в true - показывает индикатор загрузки в UI
            commit('SET_LOADING', true);
            
            // Очищаем предыдущие ошибки перед новым запросом
            commit('CLEAR_ERROR');
            
            try {
                // Базовый URL API сервера (бэкенда)
                const API_BASE = 'http://localhost:8000/api';
                
                // Выполнение GET запроса к эндпоинту /posts
                const response = await fetch(`${API_BASE}/posts`);
                
                // Проверка HTTP статуса ответа (200-299 = успех)
                // Если статус не в диапазоне 200-299, выбрасываем ошибку
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Парсинг JSON тела ответа от сервера
                const result = await response.json();
                
                // Проверка флага success в ответе от сервера
                if (result.success) {
                    // Преобразование данных из формата API в формат фронтенда
                    const formattedPosts = result.data.map(post => ({
                        id: post.id, // ID поста остается без изменений
                        text: post.content, // Переименовываем content → text для consistency
                        author: post.author ? post.author.username : 'Unknown', // Извлекаем username автора
                        date: post.DateTime, // Переименовываем DateTime → date
                        author_id: post.author_id, // ID автора для фильтрации
                        hashtags: post.hashtags, // Массив хэштегов поста
                        mentions: post.mentions // Массив упоминаний в посте
                    }));
                    
                    // Сохранение преобразованных постов в состояние Vuex
                    commit('SET_POSTS', formattedPosts);
                } else {
                    // Если сервер вернул success: false, сохраняем сообщение об ошибке
                    commit('SET_ERROR', result.message || 'Ошибка при загрузке постов');
                }
            } catch (error) {
                // Обработка сетевых ошибок или ошибок парсинга
                commit('SET_ERROR', 'Не удалось подключиться к серверу: ' + error.message);
                
                // Логирование ошибки в консоль для debugging
                console.error('Error:', error);
            } finally {
                // Выполняется в любом случае - успех или ошибка
                // Снимаем флаг загрузки чтобы скрыть индикатор
                commit('SET_LOADING', false);
            }
        },

        // Загрузка постов конкретного автора по username
        async fetchPostsByAuthor({ commit }, username) {
            // Устанавливаем флаг загрузки
            commit('SET_LOADING', true);
            
            // Очищаем ошибки
            commit('CLEAR_ERROR');
            
            try {
                const API_BASE = 'http://localhost:8000/api';
                
                // encodeURIComponent для безопасного включения username в URL
                // Защищает от специальных символов и обеспечивает валидный URL
                const url = `${API_BASE}/posts/author/${encodeURIComponent(username)}`;
                
                // GET запрос к специфичному эндпоинту автора
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Форматирование постов аналогично основному запросу
                    const formattedPosts = result.posts.map(post => ({
                        id: post.id,
                        text: post.content,
                        author: post.author ? post.author.username : 'Unknown',
                        date: post.DateTime,
                        author_id: post.author_id,
                        hashtags: post.hashtags,
                        mentions: post.mentions
                    }));
                    
                    // Сохраняем в отдельное хранилище для постов автора
                    commit('SET_AUTHOR_POSTS', formattedPosts);
                } else {
                    commit('SET_ERROR', result.message || 'Ошибка при загрузке постов автора');
                }
            } catch (error) {
                commit('SET_ERROR', 'Не удалось загрузить посты автора: ' + error.message);
                console.error('Error:', error);
            } finally {
                commit('SET_LOADING', false);
            }
        },

        // Загрузка постов по определенному хэштегу
        async fetchPostsByHashtag({ commit }, tag) {
            commit('SET_LOADING', true);
            commit('CLEAR_ERROR');
            
            try {
                const API_BASE = 'http://localhost:8000/api';
                
                // encodeURIComponent для безопасной передачи тега
                const response = await fetch(`${API_BASE}/posts/hashtag/${encodeURIComponent(tag)}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    const formattedPosts = result.posts.map(post => ({
                        id: post.id,
                        text: post.content,
                        author: post.author ? post.author.username : 'Unknown',
                        date: post.DateTime,
                        author_id: post.author_id,
                        hashtags: post.hashtags,
                        mentions: post.mentions
                    }));
                    
                    // Сохраняем в отдельное хранилище для постов с хэштегом
                    commit('SET_HASHTAG_POSTS', formattedPosts);
                } else {
                    commit('SET_ERROR', result.message || 'Ошибка при загрузке постов с хэштегом');
                }
            } catch (error) {
                commit('SET_ERROR', 'Не удалось загрузить посты с хэштегом: ' + error.message);
                console.error('Error:', error);
            } finally {
                commit('SET_LOADING', false);
            }
        },

        // Создание нового поста - POST запрос
        async createPost({ commit, dispatch }, postData) {
            commit('SET_LOADING', true);
            commit('CLEAR_ERROR');
            
            try {
                const API_BASE = 'http://localhost:8000/api';
                
                // POST запрос с телом в формате JSON
                const response = await fetch(`${API_BASE}/posts`, {
                    method: 'POST', // Метод HTTP для создания ресурса
                    headers: {
                        'Content-Type': 'application/json', // Указываем тип содержимого
                        'Accept': 'application/json' // Ожидаем JSON в ответе
                    },
                    body: JSON.stringify({ content: postData.text }) // Сериализуем данные в JSON
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Форматируем новый пост из ответа сервера
                    const newPost = {
                        id: result.data.id,
                        text: result.data.content,
                        author: result.data.author ? result.data.author.username : 'Unknown',
                        date: result.data.DateTime,
                        author_id: result.data.author_id,
                        hashtags: result.data.hashtags,
                        mentions: result.data.mentions
                    };
                    
                    // Добавляем новый пост в начало массива постов
                    commit('ADD_POST', newPost);
                    
                    // Возвращаем объект с флагом успеха для обработки в компоненте
                    return { success: true };
                } else {
                    // Сохраняем ошибку от сервера
                    commit('SET_ERROR', result.message || 'Ошибка при создании поста');
                    
                    // Возвращаем ошибку с дополнительными данными для обработки
                    return { success: false, errors: result.errors };
                }
            } catch (error) {
                // Обработка сетевых ошибок
                commit('SET_ERROR', 'Не удалось подключиться к серверу');
                console.error('Error creating post:', error);
                
                // Возвращаем флаг ошибки
                return { success: false };
            } finally {
                commit('SET_LOADING', false);
            }
        },
        
        // Синхронное действие для установки фильтра
        setFilter({ commit }, filter) {
            // Немедленно коммитим мутацию без асинхронных операций
            commit('SET_FILTER', filter);
        },
        
        // Сохранение текущего маршрута для восстановления состояния
        setCurrentRoute({ commit }, route) {
            commit('SET_CURRENT_ROUTE', route);
        },
        
        // Восстановление состояния на основе сохраненного маршрута
        navigateToSavedRoute({ state, dispatch }) {
            if (state.currentRoute) {
                // Восстанавливаем маршрут в роутере (требует импорта router)
                router.push(state.currentRoute);
                
                // Восстанавливаем данные в зависимости от типа маршрута
                if (state.currentRoute.name === 'author') {
                    // Для маршрута автора загружаем его посты
                    dispatch('fetchPostsByAuthor', state.currentRoute.params.authorName);
                } else if (state.currentRoute.name === 'hashtag') {
                    // Для маршрута хэштега загружаем посты по тегу
                    dispatch('fetchPostsByHashtag', state.currentRoute.params.tag);
                } else if (state.currentRoute.name === 'home') {
                    // Для домашней страницы загружаем основную ленту
                    dispatch('fetchPosts');
                }
            }
        },
        
        // Сброс специальных фильтров (по автору и хэштегу)
        resetSpecialFilters({ commit }) {
            commit('RESET_SPECIAL_FILTERS');
        }
    },

    // Геттеры - вычисляемые свойства на основе состояния
    // Кэшируются до изменения зависимых данных
    getters: {
        // Простой геттер для доступа к постам с хэштегом
        hashtagPosts: state => state.hashtagPosts,
        
        // Простой геттер для доступа к постам автора
        authorPosts: state => state.authorPosts,
        
        // Простой геттер для доступа к текущему маршруту
        currentRoute: state => state.currentRoute,

        // Сложный геттер для фильтрации постов по текущему активному фильтру
        filteredPosts: (state) => {
            // Временная заглушка - в реальном приложении ID должен приходить с сервера
            const currentUserId = 1;
            
            // Mock данные подписок - в реальном приложении должны быть динамическими
            const mockSubscriptions = [1, 5];
            
            // Фильтрация постов на основе currentFilter
            return state.posts.filter(post => {
                if (state.currentFilter === 'my') {
                    // Показываем только посты текущего пользователя
                    return post.author_id === currentUserId;
                } else if (state.currentFilter === 'subscribed') {
                    // Показываем посты авторов, на которых подписан пользователь
                    // Исключаем собственные посты (они уже в фильтре 'my')
                    return mockSubscriptions.includes(post.author_id) && post.author_id !== currentUserId;
                } else if (state.currentFilter === 'mentioned') {
                    // Показываем посты, где пользователь упомянут
                    return post.mentions && post.mentions.some(mention => mention.id === currentUserId);
                }
                return true; // Если фильтр не распознан, показываем все посты
            });
        },
        
        // Фабричный геттер - возвращает функцию для фильтрации по автору
        postsByAuthor: (state) => (authorName) => {
            return state.posts.filter(post => 
                // Посты, где автор совпадает или пользователь упомянут
                post.author === authorName || 
                (post.mentions && post.mentions.some(mention => mention.username === authorName))
            );
        },
        
        // Фабричный геттер - возвращает функцию для фильтрации по хэштегу
        postsByHashtag: (state) => (tag) => {
            return state.posts.filter(post => 
                // Посты, содержащие указанный хэштег
                post.hashtags && post.hashtags.some(hashtag => hashtag.name === tag)
            );
        },
        
        // Простой геттер для доступа к флагу загрузки
        isLoading: state => state.loading,
        
        // Простой геттер для доступа к ошибке
        error: state => state.error
    }
}