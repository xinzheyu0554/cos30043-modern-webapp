import { computed, ref } from "vue";
import {
  clearAuth,
  getStoredUser,
  saveAuth,
  updateStoredUser,
} from "../api/client";

export const currentUser = ref(getStoredUser());
export const isAuthenticated = computed(() => Boolean(currentUser.value));

function isPrivilegedSession() {
  const role = currentUser.value?.role;
  return role === "admin" || role === "adminstaff";
}

function clearPrivilegedAuthOnUnload() {
  if (!isPrivilegedSession()) {
    return;
  }

  return;
}

if (typeof window !== "undefined") {
  window.addEventListener("beforeunload", clearPrivilegedAuthOnUnload);
}

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

  updateStoredUser(currentUser.value);
}