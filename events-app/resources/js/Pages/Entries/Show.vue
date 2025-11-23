<template>
  <AppLayout>
    <div>
      <header class="d-flex justify-content-between align-items-center mb-3">
        <h4>Editar Entrada</h4>
        <div>
          <a href="/entries" class="btn btn-outline-secondary btn-sm">Voltar</a>
        </div>
      </header>

      <div class="card p-3">
        <form @submit.prevent="submit">
          <div class="row mb-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Status do pedido</label>
              <select v-model="currentStatus" class="form-select">
                <option value="">-- Selecionar --</option>
                <option value="Recebido">Recebido</option>
                <option value="Transporte">Transporte</option>
                <option value="Entregue">Entregue</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-secondary mt-2" @click="updateStatus" :disabled="!currentStatus">Atualizar status</button>
            </div>
          </div>
          <h6>Cliente</h6>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Nome</label>
              <input v-model="data.client.name" class="form-control" />
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">CPF</label>
              <div class="input-group">
                <input :type="cpfVisible ? 'text' : 'password'" v-model="data.client.cpf" @input="onCpfInput" class="form-control" />
                <button class="btn btn-outline-secondary" type="button" @click="cpfVisible = !cpfVisible">{{ cpfVisible ? 'Ocultar' : 'Mostrar' }}</button>
              </div>
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">Telefone</label>
              <input v-model="data.client.phone" @input="onPhoneInput" class="form-control" />
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Email</label>
              <input v-model="data.client.email" class="form-control" />
            </div>
          </div>

          <h6 class="mt-3">Endereço</h6>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Rua</label>
              <input v-model="data.client.address.street" class="form-control" />
            </div>
            <div class="col-md-2 mb-2">
              <label class="form-label">Número</label>
              <input v-model="data.client.address.number" class="form-control" />
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Complemento</label>
              <input v-model="data.client.address.complement" class="form-control" />
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Bairro</label>
              <input v-model="data.client.address.neighborhood" class="form-control" />
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Cidade</label>
              <input v-model="data.client.address.city" class="form-control" />
            </div>
            <div class="col-md-2 mb-2">
              <label class="form-label">Estado</label>
              <input v-model="data.client.address.state" class="form-control" />
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">CEP</label>
              <input v-model="data.client.address.zipcode" @input="onCepInput" class="form-control" />
            </div>
          </div>

          <h6 class="mt-3">Produtos</h6>
          <div v-for="(p, idx) in data.product" :key="idx" class="row align-items-end mb-2">
            <div class="col-md-6">
              <label class="form-label">Nome do produto</label>
              <input v-model="p.name" class="form-control" disabled />
            </div>
            <div class="col-md-2">
              <label class="form-label">Qtd</label>
              <input v-model.number="p.quantity" type="number" class="form-control" disabled />
            </div>
            <div class="col-md-3">
              <label class="form-label">Preço</label>
              <input v-model.number="p.price" type="number" step="0.01" class="form-control" disabled />
            </div>
            <div class="col-md-1">
              <button class="btn btn-danger" type="button" disabled>×</button>
            </div>
          </div>

          <div class="mb-3">
            <button type="button" class="btn btn-secondary btn-sm" disabled>Adicionar produto</button>
          </div>

          <div class="mt-3">
            <button class="btn btn-primary">Salvar</button>
          </div>
        </form>

        <div v-if="success" class="alert alert-success mt-3">{{ success }}</div>
        <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
      </div>

      <div class="card p-3 mt-3">
        <h6>Eventos</h6>
        <ul class="list-group">
          <li v-for="ev in events" :key="ev.id" class="list-group-item">
            <strong>{{ ev.status }}</strong> — {{ formatDate(ev.date) }}
          </li>
          <li v-if="events.length === 0" class="list-group-item">Nenhum evento</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { reactive, ref, onMounted } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

// we receive `id` from server-side prop when rendering via web.php
const props = defineProps({ id: [String, Number] })
const orderId = props.id || null

const success = ref(null)
const error = ref(null)

const cpfVisible = ref(false)

function maskCpf(value) {
  const v = (value || '').toString().replace(/\D/g, '').slice(0,11)
  if (v.length <= 3) return v
  if (v.length <= 6) return v.replace(/(\d{3})(\d+)/, '$1.$2')
  if (v.length <= 9) return v.replace(/(\d{3})(\d{3})(\d+)/, '$1.$2.$3')
  return v.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4')
}

function maskPhone(value) {
  const v = (value || '').toString().replace(/\D/g, '').slice(0,11)
  if (v.length <= 2) return v
  if (v.length <= 6) return v.replace(/(\d{2})(\d+)/, '($1) $2')
  if (v.length <= 10) return v.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3')
  return v.replace(/(\d{2})(\d{5})(\d+)/, '($1) $2-$3')
}

function onCpfInput(e) { data.client.cpf = maskCpf(e.target.value) }
function onPhoneInput(e) { data.client.phone = maskPhone(e.target.value) }

function maskCep(value) {
  const v = (value || '').toString().replace(/\D/g, '').slice(0,8)
  if (v.length <= 5) return v
  return v.replace(/(\d{5})(\d{1,3})/, '$1-$2')
}

function onCepInput(e) { data.client.address.zipcode = maskCep(e.target.value) }

const data = reactive({
  client: { name: '', cpf: '', email: '', phone: '', address: { neighborhood: '', street: '', number: '', complement: '', city: '', state: '', zipcode: '' } },
  product: [ { name: '', quantity: 1, price: 0.0 } ]
})

const events = ref([])
const currentStatus = ref('')

function formatDate(d) {
  if (!d) return '—'
  try {
    let parsable = d
    if (typeof d === 'string' && parsable.indexOf('T') === -1 && parsable.indexOf(' ') !== -1) {
      parsable = parsable.replace(' ', 'T')
    }
    const date = new Date(parsable)
    if (isNaN(date.getTime())) return d
    return date.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
  } catch (e) {
    return d
  }
}

function addProduct() { data.product.push({ name: '', quantity: 1, price: 0.0 }) }
function removeProduct(i) { if (data.product.length > 1) data.product.splice(i,1) }

async function load() {
  error.value = null; success.value = null
  try {
    const res = await axios.get(`/api/v1/orders/${orderId}`)
    const order = res.data
    if (!order) throw new Error('Order not found')

    // fill form
    if (order.client) {
      data.client.name = order.client.name || ''
      data.client.cpf = maskCpf(order.client.cpf || '')
      data.client.email = order.client.email || ''
      data.client.phone = maskPhone(order.client.phone || '')
        if (order.client.address) {
        data.client.address.street = order.client.address.street || ''
        data.client.address.number = order.client.address.number || ''
        data.client.address.complement = order.client.address.complement || ''
        data.client.address.neighborhood = order.client.address.neighborhood || ''
        data.client.address.city = order.client.address.city || ''
        data.client.address.state = order.client.address.state || ''
          data.client.address.zipcode = maskCep(order.client.address.zipcode || '')
      }
    }

    // products
    data.product = []
    if (order.products && order.products.length) {
      order.products.forEach(p => data.product.push({ name: p.name, quantity: p.quantity, price: p.price }))
    } else {
      data.product.push({ name: '', quantity: 1, price: 0.0 })
    }

    events.value = order.events || []
    // set currentStatus from latest event or attached field
    if (order.current_status) currentStatus.value = order.current_status
    else if (events.value && events.value.length) currentStatus.value = events.value[0].status
  } catch (e) {
    error.value = 'Falha ao carregar pedido.'
  }
}

async function updateStatus() {
  if (!currentStatus.value) return
  // confirmation dialog before sending
  try {
    const ok = window.confirm(`Confirma alteração do status para "${currentStatus.value}"?`)
    if (!ok) return
  } catch (e) {
    // if confirm fails for some reason, proceed without blocking
  }

  try {
    await axios.patch(`/api/v1/orders/${orderId}/status`, { status: currentStatus.value })
    // reload events and reflect new status
    // redirect back to dashboard after status update
    try { Inertia.visit('/dashboard') } catch (e) { window.location.href = '/dashboard' }
  } catch (e) {
    error.value = e?.response?.data?.message || 'Falha ao atualizar status'
  }
}

async function submit() {
  error.value = null; success.value = null
  try {
    // clone and sanitize: strip masks from cpf and phone before sending
    // Only send client data when saving from the edit page — products are read-only here
    const payload = JSON.parse(JSON.stringify({ client: data.client }))
    try { payload.client.cpf = (payload.client.cpf || '').toString().replace(/\D/g, '') } catch (e) {}
    try { payload.client.phone = (payload.client.phone || '').toString().replace(/\D/g, '') } catch (e) {}
    try { payload.client.address.zipcode = (payload.client.address.zipcode || '').toString().replace(/\D/g, '') } catch (e) {}

    const res = await axios.patch(`/api/v1/orders/${orderId}`, payload)
    success.value = res.data?.message || 'Atualizado com sucesso'
    // redirect to dashboard after successful edit
    try { Inertia.visit('/dashboard') } catch (e) { window.location.href = '/dashboard' }
  } catch (e) {
    if (e?.response?.data && typeof e.response.data === 'object') {
      error.value = e.response.data.message || 'Erro ao salvar'
    } else {
      error.value = 'Erro ao salvar'
    }
  }
}

onMounted(() => { if (orderId) load() })
</script>

<style scoped>
.card { border-radius: 8px }
</style>
