<template>
  <div class="home-page">
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
      <nav>
        <a href="/orders" class="btn btn-outline-light btn-sm">Consultar Pedido</a>
      </nav>
    </header>

    <section class="lead container py-5">
      <div class="row align-items-center">
      <div class="col-md-7">
        <h2 class="display-6 mb-3">Visibilidade e controle de entregas em tempo real</h2>
        <p class="lead text-muted">Rastreie eventos de pedidos, visualize status e receba atualizações automáticas para cada remessa.</p>
        <div class="mt-4">
          <a href="/orders" class="btn btn-primary btn-lg">Consultar Pedido</a>
        </div>
      </div>
      <div class="col-md-5 text-center">
        <div class="hero-illustration p-4 rounded bg-white shadow-sm">
          <svg width="220" height="160" viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="220" height="160" rx="8" fill="#f8fafc" />
            <g fill="#0d6efd">
              <rect x="20" y="40" width="80" height="18" rx="6" />
              <rect x="20" y="70" width="140" height="18" rx="6" />
              <rect x="20" y="100" width="100" height="18" rx="6" />
            </g>
          </svg>
        </div>
      </div>
      
    </div>
    </section>
  </div>
</template>

<script setup>
// Simple static homepage; no JS logic required for now
</script>

<style scoped>
.brand-header { background: linear-gradient(90deg, #0b5ed7 0%, #0d6efd 100%); color: #fff; border-radius: 0; margin-top: 0; }
.brand-title { letter-spacing: 0.3px; font-weight: 700 }
.brand-sub { opacity: 0.9 }
.hero-illustration { max-width: 320px; margin: 0 auto }
</style>
