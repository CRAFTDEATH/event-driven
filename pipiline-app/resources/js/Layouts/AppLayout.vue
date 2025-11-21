<template>
  <div>
    <header class="brand-header p-4 mb-0 d-flex align-items-center justify-content-between">
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
      <nav>
        <!-- optional right-side actions injected by pages -->
        <slot name="nav" />
      </nav>
    </header>

    <main>
      <slot />
    </main>
  </div>
</template>

<style>
/* Branding / color tokens from Show.vue applied globally */
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
</style>