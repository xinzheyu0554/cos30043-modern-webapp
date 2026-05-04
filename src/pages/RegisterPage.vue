<script setup>
import { ref } from "vue";
import { apiRequest, saveAuth } from "../api/client";

const emit = defineEmits(["register-success"]);

const username = ref("");
const email = ref("");
const password = ref("");
const message = ref("");

async function register() {
  try {
    message.value = "";

    const result = await apiRequest("register.php", {
      method: "POST",
      body: JSON.stringify({
        username: username.value,
        email: email.value,
        password: password.value,
      }),
    });

    saveAuth(result.data.token, result.data.user);
    emit("register-success", result.data.user);
  } catch (error) {
    message.value = error.message;
  }
}
</script>

<template>
  <div>
    <h2>Register</h2>

    <form @submit.prevent="register">
      <div>
        <label>Username</label>
        <input v-model="username" type="text" />
      </div>

      <div>
        <label>Email</label>
        <input v-model="email" type="email" />
      </div>

      <div>
        <label>Password</label>
        <input v-model="password" type="password" />
      </div>

      <button type="submit">Register</button>
    </form>

    <p>{{ message }}</p>
  </div>
</template>