<script setup>
import { onMounted, ref } from "vue";
import { apiRequest } from "../api/client";

const users = ref([]);
const message = ref("");

async function loadUsers() {
  try {
    const result = await apiRequest("users.php");
    users.value = result.data || [];
  } catch (error) {
    message.value = error.message;
  }
}

async function updateRole(userId, role) {
  try {
    await apiRequest("users.php", {
      method: "PUT",
      body: JSON.stringify({
        userId,
        action: "updateRole",
        role,
      }),
    });

    await loadUsers();
  } catch (error) {
    message.value = error.message;
  }
}

async function deactivateUser(userId) {
  try {
    await apiRequest("users.php", {
      method: "DELETE",
      body: JSON.stringify({
        userId,
      }),
    });

    await loadUsers();
  } catch (error) {
    message.value = error.message;
  }
}

async function restoreUser(userId) {
  try {
    await apiRequest("users.php", {
      method: "PUT",
      body: JSON.stringify({
        userId,
        action: "restore",
      }),
    });

    await loadUsers();
  } catch (error) {
    message.value = error.message;
  }
}

onMounted(loadUsers);
</script>

<template>
  <section class="content-panel">
    <div class="section-heading">
      <div>
        <p class="section-kicker">Administrator</p>
        <h2 class="section-title">User management</h2>
      </div>
      <p class="section-copy">
        Update user roles, deactivate accounts, and restore access when needed.
      </p>
    </div>

    <p v-if="message" class="alert alert-info">{{ message }}</p>

    <div class="card surface-card">
      <div class="table-responsive">
        <table class="table align-middle mb-0 admin-table">
          <thead>
            <tr>
              <th>User ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Active</th>
              <th>Role tools</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="user in users" :key="user.userId">
              <td>{{ user.userId }}</td>
              <td>{{ user.username }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.role }}</td>
              <td>{{ user.isActive }}</td>
              <td class="table-actions">
                <button class="btn btn-sm btn-outline-dark" @click="updateRole(user.userId, 'user')">
                  Set user
                </button>
                <button class="btn btn-sm btn-outline-dark" @click="updateRole(user.userId, 'adminstaff')">
                  Set staff
                </button>
              </td>
              <td class="table-actions">
                <button class="btn btn-sm btn-outline-danger" @click="deactivateUser(user.userId)">
                  Deactivate
                </button>
                <button class="btn btn-sm btn-outline-success" @click="restoreUser(user.userId)">
                  Restore
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>
