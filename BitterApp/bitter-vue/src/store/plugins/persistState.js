/**
 * Плагин для сохранения состояния Vuex в localStorage
 * Это кастомный плагин, который добавляет persistence (сохранение) состояния
 */
export const persistStatePlugin = (store) => {
    /**
     * Фаза 1: Восстановление состояния при инициализации приложения
     * 
     * При загрузке приложения проверяем, есть ли сохраненное состояние в localStorage
     * Если есть - восстанавливаем его, чтобы пользователь продолжил с того же места
     */
    const savedState = localStorage.getItem('vuex-state');
    if (savedState) {
        try {
            // Парсим сохраненное состояние из JSON строки
            const parsedState = JSON.parse(savedState);
            
            /**
             * replaceState() - метод Vuex для полной замены текущего состояния
             * Это заменяет все состояние хранилища на восстановленное из localStorage
             */
            store.replaceState(parsedState);
            
            console.log('Состояние Vuex восстановлено из localStorage');
        } catch (error) {
            // Обработка ошибок парсинга (невалидный JSON)
            console.error('Ошибка при восстановлении состояния:', error);
            
            // Очищаем поврежденные данные
            localStorage.removeItem('vuex-state');
        }
    }

    /**
     * Фаза 2: Подписка на изменения состояния
     * 
     * store.subscribe() вызывается после КАЖДОЙ мутации в хранилище
     * Это позволяет автоматически сохранять состояние при любых изменениях
     */
    store.subscribe((mutation, state) => {
        /**
         * mutation - объект с информацией о выполненной мутации:
         *   mutation.type - тип мутации (например: 'SET_POSTS')
         *   mutation.payload - данные, переданные в мутацию
         * 
         * state - текущее состояние хранилища после применения мутации
         */
        
        try {
            /**
             * Сохраняем ВСЕ состояние хранилища в localStorage
             * JSON.stringify() преобразует объект состояния в строку
             * 
             * Важно: localStorage может хранить только строки
             */
            localStorage.setItem('vuex-state', JSON.stringify(state));
            
            // Для отладки можно логировать сохранение (в development)
            if (process.env.NODE_ENV === 'development') {
                console.log('Состояние Vuex сохранено в localStorage', mutation.type);
            }
        } catch (error) {
            /**
             * Обработка возможных ошибок:
             * - Превышение квоты localStorage (обычно 5-10MB)
             * - Циклические ссылки в состоянии
             */
            console.error('Ошибка при сохранении состояния:', error);
        }
    });
};