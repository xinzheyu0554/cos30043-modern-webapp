<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { isAuthenticated } from "../state/session";

const route = useRoute();
const router = useRouter();

const groupId = computed(() => Number(route.params.id));

const group = ref(null);
const message = ref("");
const joining = ref(false);

async function loadGroup() {
  try {
    message.value = "";
    const result = await apiRequest(`groups.php?id=${groupId.value}`);
    group.value = result.data;
  } catch (error) {
    message.value = error.message;
  }
}

async function toggleMembership() {
  if (!isAuthenticated.value) {
    router.push("/login");
    return;
  }

  try {
    joining.value = true;
    message.value = "";

    await apiRequest("groups.php", {
      method: "POST",
      body: JSON.stringify({ groupId: groupId.value }),
    });

    await loadGroup();
  } catch (error) {
    message.value = error.message;
  } finally {
    joining.value = false;
  }
}

onMounted(loadGroup);
</script>

<template>
  <section class="content-panel">
    <RouterLink to="/groups" class="back-link">
      <i class="bi bi-arrow-left"></i>
      Back to groups
    </RouterLink>

    <p v-if="message" class="alert alert-info mt-3">{{ message }}</p>

    <div v-if="group" class="mt-3">
      <article class="card surface-card overflow-hidden">
        <div class="detail-image-wrap">
          <img
            v-if="group.imageUrl"
            :src="group.imageUrl"
            class="detail-image"
            :alt="group.name"
          />
        </div>

        <div class="card-body p-4 p-lg-5">
          <div class="content-meta-row mb-3">
            <span class="content-chip">{{ group.category }}</span>
            <span v-if="group.isMember" class="content-subtle text-success">
              <i class="bi bi-check-circle me-1"></i>Joined
            </span>
          </div>

          <h2 class="section-title">{{ group.name }}</h2>

          <p class="content-subtle">
            Created by {{ group.creatorName }}
          </p>

          <p class="content-subtle mb-3">
            <i class="bi bi-people me-1"></i>
            {{ group.memberCount }}
            {{ group.memberCount === 1 ? "member" : "members" }}
          </p>

          <p class="detail-body">{{ group.description }}</p>

          <div v-if="isAuthenticated" class="mt-4">
            <button
              class="btn"
              :class="group.isMember ? 'btn-outline-dark' : 'btn-accent'"
              :disabled="joining"
              @click="toggleMembership"
            >
              <template v-if="joining">Processing...</template>
              <template v-else-if="group.isMember">
                <i class="bi bi-box-arrow-right me-1"></i> Leave group
              </template>
              <template v-else>
                <i class="bi bi-plus-circle me-1"></i> Join group
              </template>
            </button>
          </div>

          <p v-else class="text-muted mt-4 mb-0">
            Login to join this group.
          </p>
        </div>
      </article>

      <div class="card surface-card mt-4">
        <div class="card-body p-4 p-lg-5">
          <p class="section-kicker">Community</p>
          <h3 class="section-title h4 mb-3">Members</h3>

          <div v-if="group.members && group.members.length" class="row g-3">
            <div
              v-for="member in group.members"
              :key="member.userId"
              class="col-12 col-md-6 col-xl-4"
            >
              <div class="d-flex align-items-center gap-3 p-3 rounded border">
                <div class="content-chip">
                  {{ member.username.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="mb-0 fw-semibold">{{ member.username }}</p>
                  <small class="content-subtle">
                    Joined {{ new Date(member.joinedAt).toLocaleDateString() }}
                  </small>
                </div>
              </div>
            </div>
          </div>

          <p v-else class="text-muted mb-0">
            No members yet. Be the first one to join.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>