<script setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { loginUser } from "../state/session";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const message = ref("");

const passwordPattern =
  /^(?=.*[A-Z])(?=.*[a-zA-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

async function register() {
  try {
    message.value = "";

    if (!passwordPattern.test(password.value)) {
      message.value =
        "Password must be at least 8 characters and include 1 uppercase letter, 1 letter, 1 number, and 1 special character.";
      return;
    }

    if (password.value !== confirmPassword.value) {
      message.value = "Password and confirm password do not match.";
      return;
    }

    const result = await apiRequest("register.php", {
      method: "POST",
      body: JSON.stringify({
        username: username.value,
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
      <div class="col-12 col-md-10 col-lg-7 col-xl-6">
        <div class="card surface-card">
          <div class="card-body p-4 p-lg-5">
            <p class="section-kicker">New Account</p>
            <h2 class="section-title mb-3">Register</h2>

            <form class="row g-3" @submit.prevent="register">
              <div class="col-12">
                <label class="form-label">Username</label>
                <input v-model="username" class="form-control" type="text" />
              </div>

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
                <label class="form-label">Confirm Password</label>
                <input
                  v-model="confirmPassword"
                  class="form-control"
                  type="password"
                />
              </div>

              <div class="col-12">
                <p class="text-muted small mb-0">
                  Password must be at least 8 characters and include 1 uppercase
                  letter, 1 number, and 1 special character.
                </p>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-accent w-100">
                  Register
                </button>
              </div>
            </form>

            <p v-if="message" class="alert alert-warning mt-4 mb-0">{{ message }}</p>
            <p class="text-muted mt-4 mb-0">
              Already registered?
              <RouterLink to="/login">Go to login</RouterLink>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
