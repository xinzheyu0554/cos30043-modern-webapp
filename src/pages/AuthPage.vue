<script setup>
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { loginUser } from "../state/session";

const route = useRoute();
const router = useRouter();

const username = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const message = ref("");
const mode = ref(route.path === "/register" ? "register" : "login");

const passwordPattern =
  /^(?=.*[A-Z])(?=.*[a-zA-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

const isRegisterMode = computed(() => mode.value === "register");

watch(
  () => route.path,
  (path) => {
    mode.value = path === "/register" ? "register" : "login";
    message.value = "";
  }
);

async function submitAuth() {
  try {
    message.value = "";

    if (isRegisterMode.value) {
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

      username.value = "";
      confirmPassword.value = "";
      message.value = "Registration successful. Please login to continue.";
      mode.value = "login";
      await router.replace("/login");
      return;
    }

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

function switchMode(nextMode) {
  if (nextMode === mode.value) return;

  message.value = "";
  mode.value = nextMode;
  router.replace(nextMode === "register" ? "/register" : "/login");
}
</script>

<template>
  <section class="auth-panel">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8 col-xl-7">
        <div class="card surface-card auth-shell">
          <div class="card-body p-4 p-lg-5">
            <div class="auth-mark">
              <div class="auth-mark-icon">
                <i class="bi" :class="isRegisterMode ? 'bi-person-plus' : 'bi-shield-lock'"></i>
              </div>
            </div>
            <p class="section-kicker">Account Access</p>
            <h2 class="section-title mb-3">
              {{ isRegisterMode ? "Create your MyWay account" : "Welcome back to MyWay" }}
            </h2>
            <p class="auth-intro-copy">
              {{
                isRegisterMode
                  ? "Sign up to save favourites, join conversations, and share experiences."
                  : "Login to continue exploring, commenting, and managing your account."
              }}
            </p>

            <form class="row g-3 mt-4" @submit.prevent="submitAuth">
              <div v-if="isRegisterMode" class="col-12">
                <label class="form-label">Username</label>
                <input v-model="username" class="form-control" type="text" />
              </div>

              <div class="col-12">
                <label class="form-label">Email</label>
                <input v-model="email" class="form-control" type="email" />
              </div>

              <div class="col-12">
                <label class="form-label">Password</label>
                <input v-model="password" class="form-control" type="password" />
              </div>

              <div v-if="isRegisterMode" class="col-12">
                <label class="form-label">Confirm Password</label>
                <input
                  v-model="confirmPassword"
                  class="form-control"
                  type="password"
                />
              </div>

              <div v-if="isRegisterMode" class="col-12">
                <p class="text-muted small mb-0">
                  Password must be at least 8 characters and include 1 uppercase
                  letter, 1 number, and 1 special character.
                </p>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-accent w-100">
                  {{ isRegisterMode ? "Create account" : "Login" }}
                </button>
              </div>
            </form>

            <p v-if="message" class="alert alert-warning mt-4 mb-0">{{ message }}</p>

            <p class="auth-footer-copy mt-4 mb-0">
              {{
                isRegisterMode
                  ? "Already registered?"
                  : "Need an account?"
              }}
              <button
                type="button"
                class="auth-inline-link"
                @click="switchMode(isRegisterMode ? 'login' : 'register')"
              >
                {{ isRegisterMode ? "Go to login" : "Create one here" }}
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
