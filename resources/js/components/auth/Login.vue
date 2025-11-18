<template>
  <div class="max-w-md mx-3 mt-10 p-6 rounded-md shadow-lg bg-white">

    <div class="flex justify-center mb-6">
      <img :src="Logo" alt="Company Logo" class="h-25 w-50" />
    </div>

    <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>

    <form @submit.prevent="submitLogin">
      <TextInput
        label="User Name"
        v-model="form.user_name"
        required
        :error="error"
      />

      <TextInput
        label="Password"
        type="password"
        v-model="form.password"
        required
      />

      <ButtonSubmit :disabled="loading" class="mt-4 bg-gradient-to-r from-teal-300 via-cyan-300 to-sky-300">
        <span v-if="loading">Logging in...</span>
        <span v-else>Login</span>
      </ButtonSubmit>
    </form>

    <Footer/>
  </div>
</template>

<script>
import axios from "axios";
import Logo from "./logo.png";

import TextInput from "../ui/TextInput.vue";
import ButtonSubmit from "../ui/ButtonSubmit.vue";
import Footer from "../ui/Footer.vue";

export default {
  name: "Login",
  components: {
    TextInput,
    ButtonSubmit,
    Footer,
  },
  data() {
    return {
      Logo,
      form: {
        user_name: "",
        password: "",
      },
      loading: false,
      error: null,
    };
  },
  methods: {
    async submitLogin() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post("/login", this.form);
        window.location.href = response.request.responseURL;
      } catch (err) {
        if (err.response?.data?.errors) {
          this.error = Object.values(err.response.data.errors)[0][0];
        } else {
          this.error = "Login service unavailable";
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
body {
  background-color: #f3f4f6;
}
</style>
