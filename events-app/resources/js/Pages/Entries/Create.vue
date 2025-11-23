<template>
  <AppLayout>
    <div>
      <h4>Nova Entrada (Order)</h4>

      <div class="card p-3 mt-3">
        <form @submit.prevent="submit">
        <h6>Cliente</h6>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label">Nome</label>
              <input v-model="data.client.name" :class="['form-control', { 'is-invalid': fieldErrors['client.name'] }]" />
              <div v-if="fieldErrors['client.name']" class="invalid-feedback">{{ fieldErrors['client.name'] }}</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">CPF</label>
            <div class="input-group">
              <input :type="cpfVisible ? 'text' : 'password'" v-model="data.client.cpf" @input="onCpfInput" :class="['form-control', { 'is-invalid': fieldErrors['client.cpf'] }]" />
              <button class="btn btn-outline-secondary" type="button" @click="cpfVisible = !cpfVisible">{{ cpfVisible ? 'Ocultar' : 'Mostrar' }}</button>
            </div>
            <div v-if="fieldErrors['client.cpf']" class="invalid-feedback">{{ fieldErrors['client.cpf'] }}</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Email</label>
              <input v-model="data.client.email" type="email" :class="['form-control', { 'is-invalid': fieldErrors['client.email'] }]" />
              <div v-if="fieldErrors['client.email']" class="invalid-feedback">{{ fieldErrors['client.email'] }}</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Telefone</label>
            <input v-model="data.client.phone" @input="onPhoneInput" :class="['form-control', { 'is-invalid': fieldErrors['client.phone'] }]" />
            <div v-if="fieldErrors['client.phone']" class="invalid-feedback">{{ fieldErrors['client.phone'] }}</div>
          </div>
        </div>

        <h6 class="mt-3">Endereço</h6>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label">Rua</label>
              <input v-model="data.client.address.street" :class="['form-control', { 'is-invalid': fieldErrors['client.address.street'] }]" />
              <div v-if="fieldErrors['client.address.street']" class="invalid-feedback">{{ fieldErrors['client.address.street'] }}</div>
          </div>
          <div class="col-md-2 mb-2">
            <label class="form-label">Número</label>
              <input v-model="data.client.address.number" :class="['form-control', { 'is-invalid': fieldErrors['client.address.number'] }]" />
              <div v-if="fieldErrors['client.address.number']" class="invalid-feedback">{{ fieldErrors['client.address.number'] }}</div>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">Complemento</label>
              <input v-model="data.client.address.complement" :class="['form-control', { 'is-invalid': fieldErrors['client.address.complement'] }]" />
              <div v-if="fieldErrors['client.address.complement']" class="invalid-feedback">{{ fieldErrors['client.address.complement'] }}</div>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">Bairro</label>
              <input v-model="data.client.address.neighborhood" :class="['form-control', { 'is-invalid': fieldErrors['client.address.neighborhood'] }]" />
              <div v-if="fieldErrors['client.address.neighborhood']" class="invalid-feedback">{{ fieldErrors['client.address.neighborhood'] }}</div>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">Cidade</label>
              <input v-model="data.client.address.city" :class="['form-control', { 'is-invalid': fieldErrors['client.address.city'] }]" />
              <div v-if="fieldErrors['client.address.city']" class="invalid-feedback">{{ fieldErrors['client.address.city'] }}</div>
          </div>
          <div class="col-md-2 mb-2">
            <label class="form-label">Estado</label>
              <input v-model="data.client.address.state" :class="['form-control', { 'is-invalid': fieldErrors['client.address.state'] }]" />
              <div v-if="fieldErrors['client.address.state']" class="invalid-feedback">{{ fieldErrors['client.address.state'] }}</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">CEP</label>
            <div class="input-group">
              <input v-model="data.client.address.zipcode" @input="onCepInput" :class="['form-control', { 'is-invalid': fieldErrors['client.address.zipcode'] }]" @blur="lookupCep" />
              <button class="btn btn-outline-secondary" type="button" @click="lookupCep" :disabled="cepLoading">
                <span v-if="!cepLoading"><i class="fa-solid fa-magnifying-glass"></i></span>
                <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              </button>
            </div>
            <div v-if="fieldErrors['client.address.zipcode']" class="invalid-feedback">{{ fieldErrors['client.address.zipcode'] }}</div>
          </div>
        </div>

        <h6 class="mt-3">Produtos</h6>
        <div v-for="(p, idx) in data.product" :key="idx" class="row align-items-end mb-2">
          <div class="col-md-6">
            <label class="form-label">Nome do produto</label>
              <input v-model="p.name" :class="['form-control', { 'is-invalid': fieldErrors[`product.${idx}.name`] }]" />
              <div v-if="fieldErrors[`product.${idx}.name`]" class="invalid-feedback">{{ fieldErrors[`product.${idx}.name`] }}</div>
          </div>
          <div class="col-md-2">
            <label class="form-label">Qtd</label>
              <input v-model.number="p.quantity" type="number" :class="['form-control', { 'is-invalid': fieldErrors[`product.${idx}.quantity`] }]" />
              <div v-if="fieldErrors[`product.${idx}.quantity`]" class="invalid-feedback">{{ fieldErrors[`product.${idx}.quantity`] }}</div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Preço</label>
              <input v-model.number="p.price" type="number" step="0.01" :class="['form-control', { 'is-invalid': fieldErrors[`product.${idx}.price`] }]" />
              <div v-if="fieldErrors[`product.${idx}.price`]" class="invalid-feedback">{{ fieldErrors[`product.${idx}.price`] }}</div>
          </div>
          <div class="col-md-1">
            <button class="btn btn-danger" type="button" @click="removeProduct(idx)">×</button>
          </div>
        </div>

        <div class="mb-3">
          <button type="button" class="btn btn-secondary btn-sm" @click="addProduct">Adicionar produto</button>
        </div>

        <div class="mt-3">
          <button class="btn btn-primary">Enviar</button>
        </div>
      </form>

      <div v-if="success" class="alert alert-success mt-3">{{ success }}</div>
      <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { reactive, ref } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const success = ref(null)
const error = ref(null)
const fieldErrors = ref({})

const data = reactive({
  client: {
    name: '', cpf: '', email: '', phone: '', address: {
      neighborhood: '', street: '', number: '', complement: '', city: '', state: '', zipcode: ''
    }
  },
  product: [ { name: '', quantity: 1, price: 0.0 } ]
})

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

const cepLoading = ref(false)

async function lookupCep() {
  try {
    // clear previous zipcode error
    if (fieldErrors.value['client.address.zipcode']) delete fieldErrors.value['client.address.zipcode']
  } catch (e) {}

  const raw = (data.client.address.zipcode || '').toString()
  const cep = raw.replace(/\D/g, '')
  if (!cep || cep.length !== 8) {
    // do nothing if not a full CEP
    return
  }

  cepLoading.value = true
  try {
    const res = await axios.get(`https://viacep.com.br/ws/${cep}/json/`)
    if (res.data && !res.data.erro) {
      data.client.address.street = res.data.logradouro || data.client.address.street
      data.client.address.neighborhood = res.data.bairro || data.client.address.neighborhood
      data.client.address.city = res.data.localidade || data.client.address.city
      data.client.address.state = res.data.uf || data.client.address.state
    } else {
      fieldErrors.value['client.address.zipcode'] = 'CEP não encontrado.'
    }
  } catch (e) {
    fieldErrors.value['client.address.zipcode'] = 'Falha ao buscar CEP.'
  } finally {
    cepLoading.value = false
  }
}

function addProduct() { data.product.push({ name: '', quantity: 1, price: 0.0 }) }
function removeProduct(i) { if (data.product.length > 1) data.product.splice(i,1) }

function validate() {
  fieldErrors.value = {}
  if (!data.client.name) fieldErrors.value['client.name'] = 'Nome é obrigatório.'
  if (!data.client.cpf) fieldErrors.value['client.cpf'] = 'CPF é obrigatório.'
  if (!data.client.email) fieldErrors.value['client.email'] = 'Email é obrigatório.'
  else if (!/^\S+@\S+\.\S+$/.test(data.client.email)) fieldErrors.value['client.email'] = 'Email inválido.'
  if (!data.client.phone) fieldErrors.value['client.phone'] = 'Telefone é obrigatório.'
  // address
  const addr = data.client.address
  if (!addr.street) fieldErrors.value['client.address.street'] = 'Rua é obrigatória.'
  if (!addr.number) fieldErrors.value['client.address.number'] = 'Número é obrigatório.'
  if (!addr.neighborhood) fieldErrors.value['client.address.neighborhood'] = 'Bairro é obrigatório.'
  if (!addr.city) fieldErrors.value['client.address.city'] = 'Cidade é obrigatória.'
  if (!addr.state) fieldErrors.value['client.address.state'] = 'Estado é obrigatório.'
  if (!addr.zipcode) fieldErrors.value['client.address.zipcode'] = 'CEP é obrigatório.'

  // products
  data.product.forEach((p, idx) => {
    if (!p.name) fieldErrors.value[`product.${idx}.name`] = 'Nome do produto é obrigatório.'
    if (!p.quantity || p.quantity <= 0) fieldErrors.value[`product.${idx}.quantity`] = 'Quantidade inválida.'
    if (p.price === null || p.price === undefined) fieldErrors.value[`product.${idx}.price`] = 'Preço inválido.'
  })

  return Object.keys(fieldErrors.value).length === 0
}

async function submit() {
  error.value = null; success.value = null
  if (!validate()) return
  try {
    // prepare payload: remove masks for cpf and phone before sending
    const payload = JSON.parse(JSON.stringify(data))
    try { payload.client.cpf = (payload.client.cpf || '').toString().replace(/\D/g, '') } catch (e) {}
    try { payload.client.phone = (payload.client.phone || '').toString().replace(/\D/g, '') } catch (e) {}
    try { payload.client.address.zipcode = (payload.client.address.zipcode || '').toString().replace(/\D/g, '') } catch (e) {}

    const token = (typeof localStorage !== 'undefined') ? localStorage.getItem('token') : null
    const headers = token ? { Authorization: `Bearer ${token}` } : {}
    const res = await axios.post('/api/v1/orders', payload, { headers })
    success.value = res?.data?.message || 'Criado com sucesso'
    // redirect to dashboard after successful create
    try { Inertia.visit('/dashboard') } catch (e) { window.location.href = '/dashboard' }
  } catch (e) {
    if (e?.response?.data && typeof e.response.data === 'object') {
      // if server returns validation errors
      error.value = e.response.data.message || 'Erro ao enviar'
      if (e.response.data.errors) fieldErrors.value = { ...fieldErrors.value, ...e.response.data.errors }
    } else {
      error.value = 'Erro ao enviar'
    }
  }
}
</script>

<style scoped>
.card { border-radius: 8px }
</style>
