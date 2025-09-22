<template>
  <!-- 
    Корневой элемент приложения с идентификатором "app"
    Этот элемент служит контейнером для всего приложения
  -->
  <div id="app">
    <!-- 
      Компонент <router-view> от Vue Router
      Отображает компонент, соответствующий текущему маршруту
      Это динамическая область, где меняются страницы приложения
      при навигации между разными URL
    -->
    <router-view />
  </div>
</template>

<script>
// Экспорт компонента App по умолчанию
export default {
  // Имя компонента - используется для отладки и рекурсивных ссылок
  name: 'App'
  
  // В этом компоненте нет логики setup() или data(), так как он выполняет
  // только роль обертки для маршрутизатора (router-view)
}
</script>

<style>
body {
  background-color: #f5fff5 !important;
  margin: 0;
  padding: 20px;
  min-height: 100vh;
}

.main-nav {
  background-color: #4CAF50;
  padding: 6px;
  border-radius: 4px;
  width: calc(100% - 12px);
  box-sizing: border-box;
  margin: 0 6px 20px 6px;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: bold;
  display: block;
  padding: 10px 0;
  text-align: center;
}

.nav-link:hover {
  text-decoration: underline;
}
</style>