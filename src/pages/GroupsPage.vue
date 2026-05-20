<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { isAuthenticated } from "../state/session";

const router = useRouter();

const groups = ref([]);
const search = ref("");
const category = ref("");
const message = ref("");
const isLoading = ref(false);

const myGroups = computed(() => groups.value.filter((g) => g.isMember));

async function loadGroups() {
  try {
    isLoading.value = true;
    message.value = "";

    const query = new URLSearchParams({
      search: search.value,
      category: category.value,
    });

    const result = await apiRequest(`groups.php?${query.toString()}`);
    groups.value = Array.isArray(result.data) ? result.data : [];
  } catch (error) {
    message.value = error.message;
  } finally {
    isLoading.value = false;
  }
}

function submitSearch() {
  loadGroups();
}

function openGroup(groupId) {
  router.push(`/groups/${groupId}`);
}

onMounted(loadGroups);
</script>

<template>
  <section class="content-panel">
    <div class="section-heading">
      <div>
        <p class="section-kicker">Community</p>
        <h2 class="section-title">Groups &amp; Meetups</h2>
      </div>
      <p class="section-copy">
        Find your people. Browse student-run groups, join communities that
        match your interests, and connect with others on campus.
      </p>
    </div>

    <div v-if="isAuthenticated && myGroups.length" class="mb-5">
      <h3 class="section-title h3 mb-3">My Groups</h3>

      <div class="row g-4">
        <div
          v-for="group in myGroups"
          :key="group.groupId"
          class="col-12 col-md-6 col-xl-4"
        >
          <article class="card h-100 surface-card content-card">
            <img
              v-if="group.imageUrl"
              :src="group.imageUrl"
              class="card-img-top content-image"
              alt="group image"
            />

            <div class="card-body d-flex flex-column">
              <div class="content-meta-row">
                <span class="content-chip">{{ group.category }}</span>
                <span class="content-subtle text-success">
                  <i class="bi bi-check-circle me-1"></i>Joined
                </span>
              </div>

              <h3 class="content-card-title mt-3">{{ group.name }}</h3>

              <p class="content-subtle mb-2">
                <i class="bi bi-people me-1"></i>
                {{ group.memberCount }}
                {{ group.memberCount === 1 ? "member" : "members" }}
              </p>

              <button
                class="btn btn-outline-dark mt-auto"
                @click="openGroup(group.groupId)"
              >
                View group
              </button>
            </div>
          </article>
        </div>
      </div>
    </div>

    <div class="card surface-card filter-card">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-5">
            <label class="form-label">Search</label>
            <input
              v-model="search"
              class="form-control"
              type="text"
              placeholder="Find by name or description"
              @keyup.enter="submitSearch"
            />
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Category</label>
            <input
              v-model="category"
              class="form-control"
              type="text"
              placeholder="e.g. Social"
              @keyup.enter="submitSearch"
            />
          </div>

          <div class="col-12 col-md-3">
            <button class="btn btn-accent w-100" @click="submitSearch">
              Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="message" class="alert alert-warning mt-4">{{ message }}</p>

    <p v-if="isLoading" class="text-muted mt-4">Loading groups...</p>

    <div v-else class="row g-4 mt-1">
      <div
        v-for="group in groups"
        :key="group.groupId"
        class="col-12 col-md-6 col-xl-4"
      >
        <article class="card h-100 surface-card content-card">
          <img
            v-if="group.imageUrl"
            :src="group.imageUrl"
            class="card-img-top content-image"
            alt="group image"
          />

          <div class="card-body d-flex flex-column">
            <div class="content-meta-row">
              <span class="content-chip">{{ group.category }}</span>
              <span v-if="group.isMember" class="content-subtle text-success">
                <i class="bi bi-check-circle me-1"></i>Joined
              </span>
            </div>

            <h3 class="content-card-title mt-3">{{ group.name }}</h3>

            <p class="content-subtle mb-2">
              <i class="bi bi-people me-1"></i>
              {{ group.memberCount }}
              {{ group.memberCount === 1 ? "member" : "members" }}
            </p>

            <p class="card-text flex-grow-1 content-excerpt">
              {{ group.description }}
            </p>

            <button
              class="btn btn-outline-dark mt-3"
              @click="openGroup(group.groupId)"
            >
              View group
            </button>
          </div>
        </article>
      </div>
    </div>

    <div v-if="!isLoading && groups.length === 0" class="alert alert-info mt-4">
      No groups found.
    </div>
  </section>
</template>