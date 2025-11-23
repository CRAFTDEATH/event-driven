<template>
  <AppLayout>
    <div>
      <header class="d-flex justify-content-between align-items-center mb-3">
        <h4>Dashboard</h4>
      </header>

      <div class="card p-3 mb-3">
        <form class="row g-2 align-items-end" @submit.prevent="applyFilters">
          <div class="col-md-3">
            <label class="form-label">Nome do cliente</label>
            <input v-model="filters.name" class="form-control" placeholder="Buscar por nome" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Código</label>
            <input v-model="filters.codigo" class="form-control" placeholder="Código do pedido" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">Todos</option>
              <option value="Recebido">Recebido</option>
              <option value="Transporte">Transporte</option>
              <option value="Entregue">Entregue</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary me-2">Filtrar</button>
            <button type="button" class="btn btn-outline-secondary" @click="clearFilters">Limpar</button>
          </div>
        </form>
      </div>

      <div class="card p-3">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Nome do Cliente</th>
                <th>Código</th>
                <th>Status Atual</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td>{{ order.client?.name || '—' }}</td>
                <td>{{ order.codigo }}</td>
                <td>{{ order.current_status || '—' }}</td>
                <td>
                  <a :href="`/entries/${order.id}`" class="btn btn-sm btn-outline-primary">Ver</a>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <nav aria-label="pagination" class="mt-3">
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: !meta.prev_page_url }">
              <button class="page-link" @click="changePage(meta.current_page - 1)" :disabled="!meta.prev_page_url">Anterior</button>
            </li>
            <li class="page-item" v-for="p in pages" :key="p" :class="{ active: p === meta.current_page }">
              <button class="page-link" @click="changePage(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: !meta.next_page_url }">
              <button class="page-link" @click="changePage(meta.current_page + 1)" :disabled="!meta.next_page_url">Próxima</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const orders = ref([])
const meta = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, prev_page_url: null, next_page_url: null })
const filters = ref({ name: '', codigo: '', status: '', per_page: 10 })
const loading = ref(false)

function buildPages() {
  const pages = []
  const last = meta.value.last_page || 1
  for (let i = 1; i <= last; i++) pages.push(i)
  return pages
}

const pages = computed(() => buildPages())

async function fetchOrders(page = 1) {
  loading.value = true
  try {
    const params = { ...filters.value, page }
    const res = await axios.get('/api/v1/orders', { params })
    const data = res.data || { data: [], meta: {} }
    orders.value = data.data || []
    // Laravel paginator wraps pagination meta under 'meta' or 'links'
    if (data.meta) {
      meta.value = {
        current_page: data.meta.current_page || 1,
        last_page: data.meta.last_page || 1,
        per_page: data.meta.per_page || filters.value.per_page,
        total: data.meta.total || 0,
        prev_page_url: data.links?.prev || null,
        next_page_url: data.links?.next || null,
      }
    } else {
      meta.value = { current_page: 1, last_page: 1, per_page: filters.value.per_page, total: orders.value.length, prev_page_url: null, next_page_url: null }
    }
  } catch (e) {
    console.warn('Failed to load orders for dashboard', e)
  } finally {
    loading.value = false
  }
}

function applyFilters() { fetchOrders(1) }
function clearFilters() { filters.value = { name: '', codigo: '', status: '', per_page: filters.value.per_page }; fetchOrders(1) }
function changePage(p) { if (p < 1 || p > meta.value.last_page) return; fetchOrders(p) }

onMounted(() => {
  fetchOrders(1)
})
</script>

<style scoped>
.card { border-radius:8px }
</style>
