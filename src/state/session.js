import { computed, ref } from "vue";
import { clearAuth, getStoredUser, saveAuth } from "../api/client";

export const currentUser = ref(getStoredUser());
export const isAuthenticated = computed(() => Boolean(currentUser.value));

export function loginUser(token, user) {
  saveAuth(token, user);
  currentUser.value = user;
}

export function logoutUser() {
  clearAuth();
  currentUser.value = null;
}

export function syncCurrentUser(updates) {
  if (!currentUser.value) return;

  currentUser.value = {
    ...currentUser.value,
    ...updates,
  };

  localStorage.setItem("user", JSON.stringify(currentUser.value));
}
