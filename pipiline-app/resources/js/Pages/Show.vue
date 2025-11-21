<template>
  <div class="container my-4">
    <header class="brand-header rounded p-3 mb-4 d-flex align-items-center justify-content-between">
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
        <a :href="'/orders/' + (orderId || '')" class="btn btn-outline-light btn-sm">Open Page</a>
      </div>
    </header>
  
    <div class="card mb-3 shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <p class="mb-1"><strong>Order ID:</strong> {{ orderId || '—' }}</p>
          <p class="text-muted small mb-0">Showing <strong>{{ events.length }}</strong> event(s)</p>
        </div>
        <div class="text-end">
          <small class="text-muted">Updated: {{ lastUpdated }}</small>
        </div>
      </div>
    </div>
  
    <div class="mt-3">
      <div v-if="events.length === 0" class="alert alert-info">No events found for this order.</div>
  
      <div v-else class="table-responsive shadow-sm rounded">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-dark">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Order ID</th>
              <th scope="col">Date</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ev, idx) in events" :key="ev.id || idx">
              <th scope="row">{{ ev.id ?? idx + 1 }}</th>
              <td class="align-middle">{{ ev.order_id }}</td>
              <td class="align-middle">{{ formatDate(ev.created_at) }}</td>
              <td class="align-middle"><span :class="['badge', statusClass(getStatus(ev))]">{{ getStatus(ev) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
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

// Initialize events if the server passed them directly; otherwise expect an order id in props.order
if (Array.isArray(props.order)) {
  events.value = props.order
} else if (props.order && typeof props.order === 'object') {
  events.value = [props.order]
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

function getStatus(ev) {
  if (!ev || !ev.payload) return '—'
  const p = ev.payload
  if (typeof p === 'string') return p
  return p?.status || p?.state || p?.status_code || '—'
}

function statusClass(status) {
  if (!status) return 'bg-secondary text-white'
  const s = String(status).toLowerCase()
  if (s.includes('deliv') || s.includes('delivered') || s.includes('ok') || s.includes('complete')) return 'bg-success text-white'
  if (s.includes('pend') || s.includes('pending')) return 'bg-warning text-dark'
  if (s.includes('cancel') || s.includes('failed') || s.includes('error')) return 'bg-danger text-white'
  if (s.includes('in transit') || s.includes('transit') || s.includes('shipped')) return 'bg-info text-white'
  return 'bg-secondary text-white'
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
  const id = orderId.value
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

.badge {
  font-weight: 600;
  padding: 0.5em 0.75em;
  border-radius: 0.5rem;
}
</style>