<script setup>
import { computed, onMounted, ref } from "vue";
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
const isPasswordModalOpen = ref(false);

const passwordPattern =
  /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

const profileInitials = computed(() => {
  const source = username.value || currentUser.value?.username || "MyWay User";

  return source
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() || "")
    .join("");
});

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

function openPasswordModal() {
  passwordMessage.value = "";
  isPasswordModalOpen.value = true;
}

function closePasswordModal() {
  isPasswordModalOpen.value = false;
  passwordMessage.value = "";
  currentPassword.value = "";
  newPassword.value = "";
  confirmPassword.value = "";
}

onMounted(loadProfile);
</script>

<template>
  <section class="content-panel">
    <div class="profile-showcase">
      <div class="row g-4">
        <div class="col-12 col-xl-4">
          <aside class="profile-sidebar">
            <div class="profile-avatar">{{ profileInitials }}</div>
            <p class="section-kicker">Account</p>
            <h2 class="profile-name">{{ username || currentUser?.username }}</h2>
            <p class="profile-subtle">{{ email || currentUser?.email }}</p>

            <div class="profile-status-grid">
              <div class="profile-status-card">
                <span class="profile-status-label">Role</span>
                <span class="profile-status-value">{{ role || currentUser?.role }}</span>
              </div>
              <div class="profile-status-card">
                <span class="profile-status-label">Access</span>
                <span class="profile-status-value">Active</span>
              </div>
            </div>

            <nav class="profile-nav">
              <button
                type="button"
                class="profile-nav-link"
                :class="{ 'is-active': true }"
              >
                <i class="bi bi-person-circle"></i>
                Profile details
              </button>
              <button
                type="button"
                class="profile-nav-link"
                @click="openPasswordModal"
              >
                <i class="bi bi-shield-lock"></i>
                Change password
              </button>
            </nav>
          </aside>
        </div>

        <div class="col-12 col-xl-8">
          <div class="profile-main-card">
            <div class="p-4 p-lg-5">
              <p class="section-kicker">Profile</p>
              <h2 class="section-title mb-3">Your details</h2>
              <p class="profile-panel-copy">
                Keep your account information up to date so your profile stays
                current across MyWay.
              </p>

              <form class="row g-3 mt-1" @submit.prevent="updateProfile">
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

              <p
                v-if="profileMessage"
                class="alert alert-info profile-inline-alert mt-4 mb-0"
              >
                {{ profileMessage }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="isPasswordModalOpen"
      class="profile-modal-backdrop"
      @click.self="closePasswordModal"
    >
      <div class="profile-modal-card">
        <div class="profile-modal-header">
          <div>
            <p class="section-kicker mb-2">Security</p>
            <h2 class="section-title h3 mb-2">Change password</h2>
            <p class="profile-panel-copy">
              Use a strong password to keep your MyWay account secure.
            </p>
          </div>
          <button
            type="button"
            class="profile-modal-close"
            @click="closePasswordModal"
            aria-label="Close password dialog"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form class="row g-3 mt-1" @submit.prevent="updatePassword">
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
              Use at least 8 characters with uppercase, lowercase, a
              number, and a special character.
            </p>
          </div>

          <div class="col-12 d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-accent">
              Update password
            </button>
            <button
              type="button"
              class="btn btn-outline-dark"
              @click="closePasswordModal"
            >
              Cancel
            </button>
          </div>
        </form>

        <p
          v-if="passwordMessage"
          class="alert alert-info profile-inline-alert mt-4 mb-0"
        >
          {{ passwordMessage }}
        </p>
      </div>
    </div>
  </section>
</template>
