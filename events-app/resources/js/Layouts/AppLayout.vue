<template>
  <div class="app-root d-flex">
    <Sidebar />
    <div class="main-column flex-fill">
      <Header />
      <main class="container py-4">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '../Components/Sidebar.vue'
import Header from '../Components/Header.vue'
import { onMounted, onBeforeUnmount } from 'vue'
import { Inertia } from '@inertiajs/inertia'

// Client-side guard: if user has no token, redirect to login.
function checkTokenAndRedirect() {
  try {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('token')
      if (!token && window.location.pathname !== '/login') {
        Inertia.visit('/login')
      }
    }
  } catch (e) {
    // ignore
  }
}

onMounted(() => {
  checkTokenAndRedirect()
  // listen for token removal from other tabs/windows and redirect
  window.addEventListener('storage', checkTokenAndRedirect)
})

onBeforeUnmount(() => {
  try { window.removeEventListener('storage', checkTokenAndRedirect) } catch (e) {}
})
</script>

<style>
/* Reuse branding colors from pipiline-app */
.app-root { min-height: 100vh; display: flex; }
.main-column { background: #f8f9fa; min-height:100vh; }
.brand-header { background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%); color: #fff; }
.brand-title { letter-spacing: 0.3px; }
.sidebar { width: 220px; background: #0d6efd; color: #fff; }
.sidebar a { color: #fff; text-decoration: none; }
.sidebar .nav-item { padding: 0.6rem 1rem; }
.sidebar .brand { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
</style>
