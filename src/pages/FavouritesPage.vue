<script setup>
import { ref, onMounted } from "vue";
import { apiRequest } from "../api/client";

const username = ref("");
const email = ref("");
const role = ref("");
const message = ref("");

async function loadProfile() {
  try {
    const result = await apiRequest("profile.php");
    const profile = result.data;

    username.value = profile.username;
    email.value = profile.email;
    role.value = profile.role;
  } catch (error) {
    message.value = error.message;
  }
}

async function updateProfile() {
  try {
    await apiRequest("profile.php", {
      method: "PUT",
      body: JSON.stringify({
        username: username.value,
        email: email.value,
      }),
    });

    message.value = "Profile updated";
  } catch (error) {
    message.value = error.message;
  }
}

onMounted(loadProfile);
</script>

<template>
  <div>
    <h2>Profile</h2>

    <form @submit.prevent="updateProfile">
      <div>
        <label>Username</label>
        <input v-model="username" />
      </div>

      <div>
        <label>Email</label>
        <input v-model="email" />
      </div>

      <p>Role: {{ role }}</p>

      <button type="submit">Update Profile</button>
    </form>

    <p>{{ message }}</p>
  </div>
</template>