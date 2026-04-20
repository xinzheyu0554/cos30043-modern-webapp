<script setup>
import { onMounted, ref } from "vue";

const users = ref([]);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const response = await fetch("http://localhost:8000/api/users.php");
    const result = await response.json();

    if (result.status === "success") {
      users.value = result.data;
    } else {
      error.value = "Failed to load users.";
    }
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="app">
    <div class="container">
      <h1>User Table Test</h1>

      <p v-if="loading">Loading users...</p>
      <p v-else-if="error">{{ error }}</p>

      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>User ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Active</th>
              <th>Created At</th>
              <th>Updated At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.userId">
              <td>{{ user.userId }}</td>
              <td>{{ user.firstName }}</td>
              <td>{{ user.lastName }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.role }}</td>
              <td>{{ user.address || "-" }}</td>
              <td>{{ user.phoneNumber || "-" }}</td>
              <td>{{ Number(user.isActive) === 1 ? "Yes" : "No" }}</td>
              <td>{{ user.createdAt }}</td>
              <td>{{ user.updatedAt || "-" }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.app {
  min-height: 100vh;
  padding: 32px;
  background: #f5f7fb;
  font-family: Arial, Helvetica, sans-serif;
  color: #1f2937;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

h1 {
  margin-bottom: 20px;
}

.table-wrapper {
  overflow-x: auto;
  background: #ffffff;
  border: 1px solid #dbe3ec;
  border-radius: 12px;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1100px;
}

thead {
  background: #eef2f7;
}

th,
td {
  padding: 12px 14px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

th {
  font-weight: 700;
}

tbody tr:hover {
  background: #f9fafb;
}
</style>