<template>
  <div>
    <header class="brand-header p-4 mb-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
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
    </header>

    <div class="login-page d-flex justify-content-center align-items-center" style="min-height:72vh;">
      <div class="card p-4 shadow-sm" style="width:420px;">
        <div class="text-center mb-3">
          <h2 class="mb-1 text-muted fs-2">Entrar</h2>
        </div>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-control form-control-lg" required />
            <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Senha</label>
            <input v-model="form.password" type="password" class="form-control form-control-lg" required />
            <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password }}</div>
        </div>

        <div class="d-grid">
          <button class="btn btn-primary w-100 py-2" type="submit">Entrar</button>
        </div>
      </form>
      <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const form = ref({ email: '', password: '' })
const error = ref(null)
const errors = ref({})

function validate() {
  errors.value = {}
  // simple email check
  if (!form.value.email) errors.value.email = 'Email é obrigatório.'
  else if (!/^\S+@\S+\.\S+$/.test(form.value.email)) errors.value.email = 'Email inválido.'

  if (!form.value.password) errors.value.password = 'Senha é obrigatória.'
  else if (form.value.password.length < 4) errors.value.password = 'Senha muito curta.'

  return Object.keys(errors.value).length === 0
}

async function submit() {
  error.value = null
  if (!validate()) return
  try {
    const res = await axios.post('/api/login', form.value)
    console.log('login response', res)
    // attempt to read token from common shapes
    const token = res?.data?.token || res?.data?.plainTextToken || res?.data?.data?.token || res?.data?.user?.token
    if (token) {
      try { localStorage.setItem('token', token) } catch (e) {}
      // persist user object for sidebar/avatar
      try { if (res?.data?.user) localStorage.setItem('user', JSON.stringify(res.data.user)) } catch (e) {}
      // prefer Inertia navigation, but fallback to hard redirect if it doesn't happen
      Inertia.visit('/dashboard')
      setTimeout(() => {
        if (typeof window !== 'undefined' && window.location.pathname !== '/dashboard') {
          window.location.href = '/dashboard'
        }
      }, 400)
    } else {
      error.value = 'Resposta inválida do servidor.' + JSON.stringify(res?.data)
    }
  } catch (e) {
    console.error('login error', e)
    error.value = e?.response?.data?.message || JSON.stringify(e?.response?.data) || 'Falha ao autenticar.'
  }
}

// demoLogin removed — login is done via the form
</script>

<style scoped>
.login-page { background: linear-gradient(180deg, #e9f2ff, #f8f9fa); }
.card { border-radius: 8px }

.brand-header {
  background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%);
  color: #fff;
  margin-top: 0;
}
.brand-logo rect { fill: #0d6efd }
.brand-title { letter-spacing: 0.3px; color: #fff }
</style>
