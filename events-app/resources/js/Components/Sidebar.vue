<template>
  <aside class="main-sidebar">
    <a href="/dashboard" class="brand-link d-flex align-items-center">
      <div class="brand-icon ms-2 me-3">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="24" height="24" rx="4" fill="#004085" />
          <path d="M6 12h12M6 7h12M6 17h12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div class="brand-text">
        <div class="h6 mb-0">TransLogistics</div>
        <small class="text-white-50">Events</small>
      </div>
    </a>

    <div class="user-panel d-flex align-items-center p-3">
      <div class="image me-2">
        <img :src="avatarUrl" alt="avatar" />
      </div>
      <div class="info text-white">
        <div class="small">Bem-vindo,</div>
        <div class="fw-bold">{{ displayName }}</div>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link" href="/dashboard"><i class="fa-solid fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>

        <li class="nav-header mt-3 text-uppercase small ps-3 text-white-50">Recebimento</li>
        <li class="nav-item ps-3">
          <a class="nav-link" href="/entries/create"><i class="fa-solid fa-plus me-2"></i> Cadastrar Entrada</a>
        </li>

        
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const user = ref(null)

onMounted(() => {
  try {
    const raw = localStorage.getItem('user')
    if (raw) user.value = JSON.parse(raw)
  } catch (e) {
    user.value = null
  }
})

const displayName = computed(() => user.value?.name || 'Usuário')
const avatarUrl = computed(() => {
  if (user.value && user.value.avatar) return user.value.avatar
  const name = user.value?.name || 'U'
  // use white background and brand-blue text for higher contrast
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=ffffff&color=0d6efd&rounded=true&size=64&bold=true`
})
</script>

<style scoped>
.main-sidebar {
  width: 250px;
  min-height: 100vh;
  background: #0d6efd; /* keep brand blue */
  color: #fff;
  display: flex;
  flex-direction: column;
}
.brand-link { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.06); text-decoration: none; color: inherit }
.brand-icon svg { vertical-align: middle }
.user-panel { border-bottom: 1px solid rgba(255,255,255,0.04) }
.user-panel .image { width: 44px; height: 44px; overflow: hidden; display:inline-block }
.user-panel .image img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; background: #fff; box-shadow: 0 6px 14px rgba(13,110,253,0.12); border: 2px solid rgba(255,255,255,0.85) }
.nav { padding: 0.5rem }
.nav-link { color: #fff; padding: 0.5rem 1rem; display: flex; align-items: center; text-decoration: none }
.nav-link:hover { background: rgba(255,255,255,0.06) }
.nav-header { padding-left: 1rem }

/* collapsed state */
.sidebar-collapsed .main-sidebar { width: 70px }
.sidebar-collapsed .brand-text, .sidebar-collapsed .user-panel .info, .sidebar-collapsed .nav-header, .sidebar-collapsed .nav-link span.text {
  display: none !important;
}
.sidebar-collapsed .nav-link { justify-content: center }
/* mobile hide/show behaviour */
@media (max-width: 767px) {
  .main-sidebar { position: fixed; z-index: 1040; top: 0; left: 0; height: 100vh; transform: translateX(0); transition: transform .2s ease-in-out; }
  .sidebar-hidden .main-sidebar { transform: translateX(-260px); }
}
</style>
