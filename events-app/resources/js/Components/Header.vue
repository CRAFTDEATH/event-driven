<template>
  <header class="brand-header p-4 mb-0 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <button class="btn btn-transparent me-3" @click="toggleSidebar" aria-label="Toggle sidebar"><i class="fa-solid fa-bars text-white"></i></button>
      <div class="brand-logo me-3">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="24" height="24" rx="4" fill="#004085" />
          <path d="M6 12h12M6 7h12M6 17h12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div>
        <h2 class="h5 mb-0 brand-title">TransLogistics</h2>
        <small class="text-white-50">Shipment Events & Tracking</small>
      </div>
    </div>

    <nav>
      <div class="d-flex align-items-center">
        <div class="me-2">
          <button v-if="!logged" class="btn btn-light btn-sm" @click="goLogin">Login</button>
          <button v-else class="btn btn-outline-light btn-sm" @click="logout">Logout</button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'

const logged = ref(false)
const isLoggingOut = ref(false)

function check() {
  try { logged.value = !!localStorage.getItem('token') } catch (e) { logged.value = false }
}

function goLogin() { Inertia.visit('/login') }

async function logout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    // attempt to revoke token on server
    await axios.post('/api/logout')
  } catch (e) {
    // ignore server errors, we'll clear local state anyway
  }
  try { localStorage.removeItem('token'); localStorage.removeItem('user') } catch (e) {}
  check()
  // Force a full reload so all stateful components are reset.
  // This aligns with "pode dar refresh na pagina pra desloga do front".
  window.location.reload()
}

check()
</script>

<script>
export default {
  methods: {
    toggleSidebar() {
      try {
        const root = document.querySelector('.app-root')
        if (!root) return
        if (window.innerWidth < 768) {
          root.classList.toggle('sidebar-hidden')
        } else {
          root.classList.toggle('sidebar-collapsed')
        }
      } catch (e) { /* ignore */ }
    }
  }
}
</script>

<style scoped>
/* Branding / color tokens from pipiline-app */
.brand-header {
  background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%);
  color: #fff;
  margin-top: 0;
}

.brand-logo rect {
  fill: #0d6efd;
}

.brand-title {
  letter-spacing: 0.3px;
}
</style>
