<template>
  <div class="app-container">
    <Sidebar />
    <div class="content">
      <RouterView />
    </div>
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
import Sidebar from './components/Sidebar.vue'
</script>

<style>
body {
    display: flex;
    min-height: 100vh;
    margin: 0;
    background-color: #f8f9fa;
}

.content {
    margin-left: 250px;
    padding: 20px;
    flex-grow: 1;
    width: calc(100% - 250px);
}
</style>
