const API_BASE = `${import.meta.env.BASE_URL}api`;
const LOCAL_AUTH_KEY = "auth:persistent";
const SESSION_AUTH_KEY = "auth:session";
const SEVEN_DAYS_MS = 7 * 24 * 60 * 60 * 1000;

function isPrivilegedRole(role) {
  return role === "admin" || role === "adminstaff";
}

function readStorage(key, storage) {
  const rawValue = storage.getItem(key);

  if (!rawValue) {
    return null;
  }

  try {
    return JSON.parse(rawValue);
  } catch {
    storage.removeItem(key);
    return null;
  }
}

function clearExpiredPersistentAuth() {
  const storedAuth = readStorage(LOCAL_AUTH_KEY, localStorage);

  if (!storedAuth) {
    return null;
  }

  if (storedAuth.expiresAt && storedAuth.expiresAt < Date.now()) {
    localStorage.removeItem(LOCAL_AUTH_KEY);
    return null;
  }

  return storedAuth;
}

function getStoredAuthRecord() {
  const sessionAuth = readStorage(SESSION_AUTH_KEY, sessionStorage);

  if (sessionAuth) {
    return sessionAuth;
  }

  return clearExpiredPersistentAuth();
}

export async function apiRequest(path, options = {}) {
  const token = getStoredToken();

  const headers = {
    ...(options.headers || {}),
  };

  if (!(options.body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }

  if (token) {
    headers["X-Auth-Token"] = token;
  }

  const response = await fetch(`${API_BASE}/${path}`, {
    ...options,
    headers,
  });

  const result = await response.json().catch(() => null);

  if (!response.ok || result?.success === false) {
    throw new Error(result?.message || "Request failed");
  }

  return result;
}

export function getStoredUser() {
  return getStoredAuthRecord()?.user || null;
}

export function getStoredToken() {
  return getStoredAuthRecord()?.token || null;
}

export function saveAuth(token, user) {
  clearAuth();

  const authRecord = {
    token,
    user,
  };

  if (isPrivilegedRole(user?.role)) {
    sessionStorage.setItem(SESSION_AUTH_KEY, JSON.stringify(authRecord));
    return;
  }

  localStorage.setItem(
    LOCAL_AUTH_KEY,
    JSON.stringify({
      ...authRecord,
      expiresAt: Date.now() + SEVEN_DAYS_MS,
    })
  );
}

export function clearAuth() {
  localStorage.removeItem(LOCAL_AUTH_KEY);
  sessionStorage.removeItem(SESSION_AUTH_KEY);
}

export function updateStoredUser(user) {
  const sessionAuth = readStorage(SESSION_AUTH_KEY, sessionStorage);

  if (sessionAuth) {
    sessionStorage.setItem(
      SESSION_AUTH_KEY,
      JSON.stringify({
        ...sessionAuth,
        user,
      })
    );
    return;
  }

  const persistentAuth = clearExpiredPersistentAuth();

  if (persistentAuth) {
    localStorage.setItem(
      LOCAL_AUTH_KEY,
      JSON.stringify({
        ...persistentAuth,
        user,
      })
    );
  }
}
