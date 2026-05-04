<script setup>
import { onMounted, ref } from "vue";
import { apiRequest } from "../api/client";
import { currentUser, syncCurrentUser } from "../state/session";

const username = ref("");
const email = ref("");
const role = ref("");
const profileMessage = ref("");
const passwordMessage = ref("");
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");

const passwordPattern =
  /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

async function loadProfile() {
  try {
    const result = await apiRequest("profile.php");
    const profile = result.data;

    username.value = profile.username;
    email.value = profile.email;
    role.value = profile.role;
  } catch (error) {
    profileMessage.value = error.message;
  }
}

async function updateProfile() {
  try {
    profileMessage.value = "";

    await apiRequest("profile.php", {
      method: "PUT",
      body: JSON.stringify({
        username: username.value,
        email: email.value,
      }),
    });

    syncCurrentUser({
      username: username.value,
      email: email.value,
    });

    profileMessage.value = "Profile updated successfully.";
  } catch (error) {
    profileMessage.value = error.message;
  }
}

async function updatePassword() {
  try {
    passwordMessage.value = "";

    if (!passwordPattern.test(newPassword.value)) {
      passwordMessage.value =
        "New password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
      return;
    }

    if (newPassword.value !== confirmPassword.value) {
      passwordMessage.value =
        "New password and confirm password do not match.";
      return;
    }

    await apiRequest("password.php", {
      method: "PUT",
      body: JSON.stringify({
        currentPassword: currentPassword.value,
        newPassword: newPassword.value,
        confirmPassword: confirmPassword.value,
      }),
    });

    currentPassword.value = "";
    newPassword.value = "";
    confirmPassword.value = "";
    passwordMessage.value = "Password updated successfully.";
  } catch (error) {
    passwordMessage.value = error.message;
  }
}

onMounted(loadProfile);
</script>

<template>
  <section class="content-panel">
    <div class="row g-4">
      <div class="col-12 col-xl-7">
        <div class="card surface-card h-100">
          <div class="card-body p-4 p-lg-5">
            <p class="section-kicker">Account</p>
            <h2 class="section-title mb-3">Your profile</h2>
            <form class="row g-3" @submit.prevent="updateProfile">
              <div class="col-12 col-md-6">
                <label class="form-label">Username</label>
                <input v-model="username" class="form-control" type="text" />
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Email</label>
                <input v-model="email" class="form-control" type="email" />
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-accent">
                  Update profile
                </button>
              </div>
            </form>

            <p v-if="profileMessage" class="alert alert-info mt-4 mb-0">
              {{ profileMessage }}
            </p>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-5">
        <div class="card surface-card h-100">
          <div class="card-body p-4 p-lg-5">
            <p class="section-kicker">Security</p>
            <h2 class="section-title h3 mb-3">Change password</h2>

            <form class="row g-3" @submit.prevent="updatePassword">
              <div class="col-12">
                <label class="form-label">Current Password</label>
                <input
                  v-model="currentPassword"
                  class="form-control"
                  type="password"
                />
              </div>

              <div class="col-12">
                <label class="form-label">New Password</label>
                <input
                  v-model="newPassword"
                  class="form-control"
                  type="password"
                />
              </div>

              <div class="col-12">
                <label class="form-label">Confirm New Password</label>
                <input
                  v-model="confirmPassword"
                  class="form-control"
                  type="password"
                />
              </div>

              <div class="col-12">
                <p class="text-muted small mb-0">
                  Use at least 8 characters with uppercase, lowercase, a number,
                  and a special character.
                </p>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-accent">
                  Update password
                </button>
              </div>
            </form>

            <p v-if="passwordMessage" class="alert alert-info mt-4 mb-0">
              {{ passwordMessage }}
            </p>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card surface-card">
          <div class="card-body p-4">
            <p class="section-kicker">Status</p>
            <h3 class="info-title">Current session</h3>
            <p class="mb-2"><strong>User:</strong> {{ currentUser?.username }}</p>
            <p class="mb-0"><strong>Role:</strong> {{ role }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
