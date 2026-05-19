import { computed, ref, watch } from "vue";

const THEME_KEY = "myway:theme";
const prefersDark =
  typeof window !== "undefined" &&
  window.matchMedia &&
  window.matchMedia("(prefers-color-scheme: dark)").matches;

export const theme = ref(loadInitialTheme());
export const isDarkMode = computed(() => theme.value === "dark");

function loadInitialTheme() {
  if (typeof window === "undefined") {
    return "light";
  }

  const savedTheme = localStorage.getItem(THEME_KEY);
  return savedTheme || (prefersDark ? "dark" : "light");
}

function applyTheme(nextTheme) {
  if (typeof document === "undefined") {
    return;
  }

  document.documentElement.setAttribute("data-theme", nextTheme);
  document.documentElement.style.colorScheme = nextTheme;
}

applyTheme(theme.value);

watch(
  theme,
  (nextTheme) => {
    if (typeof window !== "undefined") {
      localStorage.setItem(THEME_KEY, nextTheme);
    }

    applyTheme(nextTheme);
  },
  { immediate: true }
);

export function toggleTheme() {
  theme.value = theme.value === "dark" ? "light" : "dark";
}
