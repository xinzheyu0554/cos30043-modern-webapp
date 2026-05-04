<script setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { loginUser } from "../state/session";

const router = useRouter();
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

    loginUser(result.data.token, result.data.user);
    await router.push("/browse");
  } catch (error) {
    message.value = error.message;
  }
}
</script>

<template>
  <section class="auth-panel">
    <div class="row justify-content-center">
      <div class="col-12 col-md-9 col-lg-6 col-xl-5">
        <div class="card surface-card">
          <div class="card-body p-4 p-lg-5">
            <p class="section-kicker">Account Access</p>
            <h2 class="section-title mb-3">Login</h2>

            <form class="row g-3" @submit.prevent="login">
              <div class="col-12">
                <label class="form-label">Email</label>
                <input v-model="email" class="form-control" type="email" />
              </div>

              <div class="col-12">
                <label class="form-label">Password</label>
                <input
                  v-model="password"
                  class="form-control"
                  type="password"
                />
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-accent w-100">Login</button>
              </div>
            </form>

            <p v-if="message" class="alert alert-warning mt-4 mb-0">{{ message }}</p>
            <p class="text-muted mt-4 mb-0">
              Need an account?
              <RouterLink to="/register">Create one here</RouterLink>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
