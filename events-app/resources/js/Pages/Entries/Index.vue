<template>
  <AppLayout>
    <div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Entradas</h4>
        <a href="/entries/create" class="btn btn-primary btn-sm">Nova Entrada</a>
      </div>

      <div v-if="loading" class="text-center py-4">Carregando...</div>

      <div v-else>
        <div v-if="orders.length === 0" class="alert alert-info">Nenhuma entrada encontrada.</div>

        <div v-else class="table-responsive shadow-sm rounded">
          <table class="table table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Código</th>
                <th>Cliente</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(o, idx) in orders" :key="o.id || idx">
                <td>{{ o.id }}</td>
                <td>{{ o.codigo || '—' }}</td>
                <td>{{ o.client?.name || '—' }}</td>
                <td>
                  <a :href="`/orders/${o.id}`" class="btn btn-sm btn-outline-secondary">Ver</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const orders = ref([])
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const headers = token ? { Authorization: `Bearer ${token}` } : {}
    const res = await axios.get('/api/v1/orders', { headers })
    orders.value = res.data || []
  } catch (e) {
    console.error('Failed to load orders', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.table-responsive { background: #fff }
</style>
