<template>
  <div class="show-page">
    <header class="brand-header p-4 mb-0 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <div class="brand-logo me-3">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="24" height="24" rx="4" fill="#004085" />
            <path d="M6 12h12M6 7h12M6 17h12" stroke="#fff" stroke-width="1.5" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
        <div>
          <h2 class="h5 mb-0 brand-title">TransLogistics</h2>
          <small class="text-white-50">Shipment Events & Tracking</small>
        </div>
      </div>
      <div>
        <button class="btn btn-light btn-sm me-2" @click="refresh">Refresh</button>
        <a :href="'/orders/' + (orderCode || '')" class="btn btn-outline-light btn-sm">Open Page</a>
      </div>
    </header>
  
    <section class="container py-5">
      <div class="card mb-3 shadow-sm order-meta">
        <div class="card-body d-flex justify-content-between align-items-start">
          <div>
            <p class="mb-1"><strong>Cliente:</strong> {{ clientName || '—' }}</p>
            <p class="text-muted small mb-0">{{ clientCompany }}</p>
            <p class="text-muted small mt-2 mb-0"><strong>Código:</strong> {{ orderCode || '—' }}</p>
          </div>
          <div class="text-end">
            <small class="text-muted">Endereço de entrega</small>
            <div class="address mt-2">{{ formattedAddress || '—' }}</div>
          </div>
        </div>
      </div>

      <div class="card mb-4 shadow-sm panel-overview">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-1"><strong>Código:</strong> {{ orderCode || '—' }}</p>
            <p class="text-muted small mb-0">Showing <strong>{{ events.length }}</strong> event(s)</p>
          </div>
          <div class="text-end">
            <small class="text-muted">Updated: {{ lastUpdated }}</small>
          </div>
        </div>
      </div>
  
      <div class="mt-3">
        <div v-if="events.length === 0" class="alert alert-info">No events found for this order.</div>
  
        <div v-else class="table-responsive shadow-sm rounded events-table mt-4 py-3">
          <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-primary text-dark">
              <tr>
                <th scope="col" class="fw-semibold">#</th>
                <th scope="col" class="fw-semibold">Código</th>
                <th scope="col" class="fw-semibold">Date</th>
                <th scope="col" class="fw-semibold">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(ev, idx) in events" :key="ev.id || idx">
                <th scope="row">{{ ev.id ?? idx + 1 }}</th>
                <td class="align-middle">{{ getOrderCode(ev) }}</td>
                <td class="align-middle">{{ formatDate(ev.created_at) }}</td>
                <td class="align-middle"><span :class="['status-badge', statusClass(getStatus(ev))]">{{ getStatus(ev)
                    }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  order: { default: null },
  // optional token passed from server or injected on the page
  token: { default: null }
})

// If you want automatic login for testing, these credentials will be used.
// (From your DatabaseSeeder: email 'adriano.rufino@hotmail.com' / password 'password')
const AUTO_LOGIN_CREDENTIALS = {
  email: 'adriano.rufino@hotmail.com',
  password: 'password'
}

const events = ref([])
let intervalId = null

// Determine if props.order is an order object (with client/products) or an event(s) payload
const orderObj = computed(() => {
  if (!props.order) return null
  if (props.order.order) return props.order.order
  // If props.order looks like an order (has client or products or codigo)
  if (props.order.client || props.order.products || props.order.codigo) return props.order
  return null
})

// Initialize events from props.events if provided (controller passes 'events'), otherwise support common shapes
if (Array.isArray(props.events)) {
  events.value = props.events
} else {
  if (Array.isArray(props.order)) {
    events.value = props.order
  } else if (props.order && typeof props.order === 'object') {
    if (props.order.order_event) events.value = [props.order.order_event]
    else if (props.order.order_events) events.value = props.order.order_events
    else if (props.order.events) events.value = props.order.events
    else if (!orderObj.value) events.value = [props.order]
  }
}

const orderId = computed(() => {
  // If `order` prop is a primitive id (number|string), use it
  if (props.order !== null && (typeof props.order === 'number' || typeof props.order === 'string')) return props.order
  // Otherwise attempt to infer from events
  if (events.value.length && events.value[0].order_id) return events.value[0].order_id
  return null
})

const lastUpdated = computed(() => {
  if (!events.value || events.value.length === 0) return '—'
  const dates = events.value.map(e => e.created_at).filter(Boolean).map(d => new Date(d))
  if (!dates.length) return '—'
  const latest = new Date(Math.max.apply(null, dates))
  return latest.toLocaleString()
})

// Extract first payload object from events or from the order object
const firstPayload = computed(() => {
  // Prefer explicit payload object on events; if payload contains `order`, return that inner order
  for (const ev of events.value) {
    const p = ev.payload
    if (p && typeof p === 'object') {
      if (p.order && typeof p.order === 'object') return p.order
      return p
    }
  }

  // If an order object is present, prefer client/order info from it
  if (orderObj.value) return orderObj.value
  return null
})

const clientName = computed(() => {
  const o = orderObj.value
  if (o && o.client) return o.client.name || o.client.fullname || null
  const p = firstPayload.value
  if (!p) return null
  // If payload is an order object, prefer p.client.name
  if (p.client && p.client.name) return p.client.name
  return p.customer_name || p.customer?.name || p.recipient_name || p.name || p.client_name || null
})

const clientCompany = computed(() => {
  const o = orderObj.value
  if (o && o.client) return o.client.company || ''
  const p = firstPayload.value
  if (!p) return ''
  if (p.client && p.client.company) return p.client.company
  return p.customer?.company || p.company || ''
})

const formattedAddress = computed(() => {
  // Check order.client.address first
  const o = orderObj.value
  let addr = null
  if (o && o.client && o.client.address) addr = o.client.address

  if (!addr) {
    const p = firstPayload.value
    if (p) {
      // If payload is an order, check p.client.address
      if (p.client && p.client.address) addr = p.client.address
      else addr = p.address || p.shipping_address || p.delivery_address || p.recipient?.address || p.customer?.address
    }
  }

  if (!addr) return ''
  if (typeof addr === 'string') return addr
  const parts = []
  if (addr.street) parts.push(addr.street + (addr.number ? `, ${addr.number}` : ''))
  if (addr.neighborhood) parts.push(addr.neighborhood)
  if (addr.city) parts.push(addr.city + (addr.state ? ` - ${addr.state}` : ''))
  if (addr.zipcode || addr.postal_code || addr.zip) parts.push(addr.zipcode || addr.postal_code || addr.zip)
  if (addr.country) parts.push(addr.country)
  return parts.filter(Boolean).join('\n')
})

const orderCode = computed(() => {
  // Prefer explicit code on orderObj
  if (orderObj.value && orderObj.value.codigo) return orderObj.value.codigo
  // Check firstPayload for order.codigo or codigo
  const p = firstPayload.value
  if (p) {
    if (p.order && p.order.codigo) return p.order.codigo
    if (p.codigo) return p.codigo
  }
  // Fall back to first event's order_id or orderId
  if (events.value.length && events.value[0].order_id) return events.value[0].order_id
  if (orderId.value) return orderId.value
  return null
})

function getStatus(ev) {
  if (!ev) return '—'

  // Direct fields on the event
  if (ev.status) return String(ev.status)
  if (ev.order_event && ev.order_event.status) return String(ev.order_event.status)

  // Payload can be null, string, or an object that may contain nested order/event
  const p = ev.payload
  if (!p) return '—'
  if (typeof p === 'string') return p
  if (p.order_event && p.order_event.status) return String(p.order_event.status)
  return p?.status || p?.state || p?.status_code || '—'
}

function statusClass(status) {
  if (!status) return 'status-secondary'
  const s = String(status).toLowerCase()

  // Portuguese-specific mappings
  if (s.includes('entreg')) return 'status-success' // entregue, entregue com sucesso
  if (s.includes('em transporte') || s.includes('transporte') || s.includes('transito') || s.includes('em-transito')) return 'status-info' // em transporte / em trânsito
  if (s.includes('receb')) return 'status-secondary' // recebido / recebedor

  // English / generic fallbacks
  if (s.includes('deliv') || s.includes('delivered') || s.includes('ok') || s.includes('complete')) return 'status-success'
  if (s.includes('pend') || s.includes('pending')) return 'status-warning'
  if (s.includes('cancel') || s.includes('failed') || s.includes('error')) return 'status-danger'
  if (s.includes('in transit') || s.includes('transit') || s.includes('shipped')) return 'status-info'

  return 'status-secondary'
}

function getOrderCode(ev) {
  try {
    const p = ev.payload
    if (p && typeof p === 'object') {
      // if payload wraps order
      if (p.order && p.order.codigo) return p.order.codigo
      if (p.order && p.order.code) return p.order.code
      if (p.codigo) return p.codigo
    }
  } catch (e) { /* ignore */ }

  if (orderObj.value && orderObj.value.codigo) return orderObj.value.codigo
  if (ev.order_id) return ev.order_id
  if (orderId.value) return orderId.value
  return '—'
}

function formatDate(dt) {
  if (!dt) return ''
  try {
    const d = new Date(dt)
    return d.toLocaleString()
  } catch (e) {
    return dt
  }
}

async function refresh() {
  const id = orderId.value || (events.value.length ? events.value[0].order_id : null)
  if (!id) return

  try {
    const clientToken = await getOrLoginToken()

    const res = await axios.get(`/api/v1/orders/${id}/events`, {
      headers: {
        Authorization: `Bearer ${clientToken}`
      }
    })

    events.value = res.data
  } catch (e) {
    console.error('Failed to refresh events', e)
  }
}

async function getOrLoginToken() {
  // Priority: prop token -> window.Laravel.token -> localStorage keys
  if (props.token) return props.token
  if (typeof window !== 'undefined' && window.Laravel && window.Laravel.token) return window.Laravel.token
  try {
    const candidates = ['token', 'api_token', 'sanctum_token', 'auth_token']
    for (const key of candidates) {
      const v = localStorage.getItem(key)
      if (v) return v
    }
  } catch (e) {
    // ignore localStorage errors (e.g., SSR or disabled)
  }

  // If we still don't have a token, attempt to login automatically (useful for local testing)
  try {
    const res = await axios.post('/api/login', AUTO_LOGIN_CREDENTIALS)
    const token = res?.data?.token
    if (token) {
      try { localStorage.setItem('token', token) } catch (e) { }
      return token
    }
  } catch (e) {
    // login failed or not available
    console.warn('Auto-login failed', e?.response?.data || e)
  }

  return null
}

onMounted(() => {
  if (!events.value || events.value.length === 0) refresh()
  intervalId = setInterval(() => refresh(), 30000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>

<style scoped>
.brand-header {
  background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%);
  color: #fff;
}

.brand-logo rect {
  fill: #0d6efd;
}

.brand-title {
  letter-spacing: 0.3px;
}

.table-responsive {
  background: #fff;
}

.status-badge {
  display: inline-block;
  padding: 0.45rem 0.75rem;
  border-radius: 0.5rem;
  font-weight: 600;
}

.panel-overview {
  border-left: 4px solid rgba(13, 110, 253, 0.15);
}

.events-table thead {
  background: linear-gradient(180deg, rgba(13, 110, 253, 0.08), rgba(13, 110, 253, 0.02));
}

.show-page {
  padding-top: 0;
  padding-bottom: 2.5rem;
}

.brand-header {
  margin-top: 0;
}

.panel-overview {
  margin-bottom: 1.25rem;
}

.events-table {
  margin-top: 1rem;
  padding: 0.5rem;
}

.order-meta .address {
  white-space: pre-line;
  font-size: 0.95rem;
  color: #212529;
}

.order-meta .card-body { padding: 1rem 1.25rem }

/* Status color typology */
.status-success {
  background: linear-gradient(90deg, #198754, #1fae5a);
  color: #fff;
}

.status-warning {
  background: linear-gradient(90deg, #ffc107, #ffcb3a);
  color: #212529;
}

.status-danger {
  background: linear-gradient(90deg, #dc3545, #e86a7a);
  color: #fff;
}

.status-info {
  background: linear-gradient(90deg, #0dcaf0, #2fe0ff);
  color: #fff;
}

.status-secondary {
  background: linear-gradient(90deg, #6c757d, #8a9298);
  color: #fff;
}

.status-badge {
  display: inline-block;
  padding: 0.45rem 0.75rem;
  border-radius: 0.5rem;
  font-weight: 600
}
</style>