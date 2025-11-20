<template>
  <div>
    {{orderStatus}}
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
      orderStatus: this.order
    }

  },
  methods: {

  },
  mounted() {
    setInterval(async () => {
      let id = 24;

      try {
        const res = await axios.post('http://localhost:8030/api/login', {
          email: 'adriano.rufino@hotmail.com',
          password: 'password'
        });

        const token = res.data.token;

        const res2 = await axios.get(`http://localhost:8030/api/v1/orders/${id}/events`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        this.orderStatus = res2.data;
      } catch (e) {
        console.log(e)
        // Silently ignore for now; could log or surface an error
      }
    }, 60000); // 1 hora
  }
}
</script>