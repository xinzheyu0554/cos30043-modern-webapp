<script setup>
import { ref } from "vue";
import { apiRequest, saveAuth } from "../api/client";

const emit = defineEmits(["login-success"]);

const email = ref("");
const password = ref("");
const message = ref("");

async function login() {
  try {
    message.value = "";

    const result = await apiRequest("login.php", {
      method: "POST",
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });

    saveAuth(result.data.token, result.data.user);
    emit("login-success", result.data.user);
  } catch (error) {
    message.value = error.message;
  }
}
</script>

<template>
  <div>
    <h2>Login</h2>

    <form @submit.prevent="login">
      <div>
        <label>Email</label>
        <input v-model="email" type="email" />
      </div>

      <div>
        <label>Password</label>
        <input v-model="password" type="password" />
      </div>

      <button type="submit">Login</button>
    </form>

    <p>{{ message }}</p>
  </div>
</template>