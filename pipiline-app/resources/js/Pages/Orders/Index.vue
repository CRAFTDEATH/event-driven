<template>
  <div class="orders-index">
    <header class="brand-header p-4 mb-0 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <div class="brand-mark me-3">
          <svg width="46" height="46" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="24" height="24" rx="4" fill="#0d6efd" />
            <path d="M4 12h16M4 7h10M4 17h12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <h1 class="brand-title mb-0">TransLogistics</h1>
          <div class="brand-sub">Shipment Visibility & Operations</div>
        </div>
      </div>
      <div>
        <nav>
          <a href="/" class="btn btn-outline-light btn-sm">Home</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h2 class="h5 mb-3">Consultar Pedido</h2>
              <p class="text-muted small">Digite o número do pedido para visualizar o histórico de eventos e status.</p>

              <form @submit.prevent="submit" class="mt-4">
                <div class="mb-3">
                  <label class="form-label">Código do pedido</label>
                  <input v-model="codigo" type="text" class="form-control form-control-lg" placeholder="Ex: GELGCJ7IUE" required />
                </div>
                <div class="d-flex justify-content-end">
                  <button class="btn btn-primary btn-lg" :class="{ 'disabled': loading }" :disabled="loading">
                    <span v-if="!loading">Pesquisar</span>
                    <span v-else>Carregando...</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const codigo = ref('')
const loading = ref(false)

function submit() {
  if (!codigo.value) return
  loading.value = true
  // Navigate directly to the show URL. The show page will display events or a "not found" notice.
  // This avoids cases where the server returns an Inertia JSON payload but the client
  // doesn't perform the swap (missing JS bundle or runtime errors). Direct visit always navigates.
  Inertia.visit(`/orders/${encodeURIComponent(codigo.value)}`)
}
</script>

<style scoped>
.brand-header {
  background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%);
  color: #fff;
  border-radius: 0;
  margin-top: 0;
}
.brand-title { font-size: 1.25rem; font-weight: 700; letter-spacing: 0.3px }
.brand-sub { font-size: 0.9rem; opacity: 0.9 }
.brand-mark rect { fill: #0d6efd }
.card { border-radius: 0.6rem; margin-top: 1.5rem; }
.form-control-lg { padding: 0.75rem 1rem; font-size: 1rem }
.btn-primary { background-color: #0b5ed7; border-color: #0b5ed7 }
.btn-outline-light { border-color: rgba(255,255,255,0.2); color: #fff }
</style>
