<script setup>
import { ref, onMounted } from "vue";
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
  <div>
    <h2>Admin User Management</h2>

    <p>{{ message }}</p>

    <table border="1">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Active</th>
          <th>Change Role</th>
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

          <td>
            <button @click="updateRole(user.userId, 'user')">Set User</button>
            <button @click="updateRole(user.userId, 'adminstaff')">
              Set Staff
            </button>
          </td>

          <td>
            <button @click="deactivateUser(user.userId)">Deactivate</button>
            <button @click="restoreUser(user.userId)">Restore</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>