<template>
  <div>
    {{ orderStatus }}
    <p>teste</p>
  </div>
</template>

<script>
export default {
  props: {
    order: Object,
  },
  data() {
    return {
      orderStatus: this.order,
      token: null
    }
  },
  async mounted() {
    const id = 24;

    try {
      // Faz login uma vez e pega token
      const res = await axios.post('http://localhost:8030/api/login', {
        email: 'adriano.rufino@hotmail.com',
        password: 'password'
      });
      this.token = res.data.token;

      // Cria a conexão SSE, passando token via query string
      const source = new EventSource(
        `http://localhost:8030/api/v1/orders/${id}/events/stream?token=${this.token}`
      );

      // Atualiza status toda vez que o servidor enviar evento
      source.onmessage = (event) => {
        this.orderStatus = JSON.parse(event.data);
      };

      source.onerror = (err) => {
        console.error('SSE error', err);
        source.close();
      };

    } catch (e) {
      console.error('Erro ao logar ou abrir SSE', e);
    }
  }
}
</script>