<script setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { isAuthenticated } from "../state/session";
//use Route reads the current URL to get the grip id from groups id 
//and navigate to other pages 
const route = useRoute();
const router = useRouter();

//extract the group IDs from the url (groups/3 meaning groupid 3)
const groupId = computed(() => Number(route.params.id));

//reactive state 
const group = ref(null); //hold the full group data fromthe API 
const message = ref("");
const joining = ref(false); //will be true while join/leave request is in progress

//fetch the group details from the API
async function loadGroup() {
  try {
    message.value = "";
    //calls groups.php id # any number and then return the group with the member list
    const result = await apiRequest(`groups.php?id=${groupId.value}`);
    group.value = result.data;
  } catch (error) {
    message.value = error.message;
  }
}
//join or leave the group toggle
async function toggleMembership() {
  //if not logged in redirect to login page!
  if (!isAuthenticated.value) {
    router.push("/login");
    return;
  }

  try {
    joining.value = true; //show "Processing" on the button 
    message.value = "";
    //POST to groups.php with the groupID and also backend checks if they are already a member then it removes them if not add them 
    await apiRequest("groups.php", {
      method: "POST",
      body: JSON.stringify({ groupId: groupId.value }),
    });
    // reload the group data to reflect the updated membership status and member list
    await loadGroup();
  } catch (error) {
    message.value = error.message;
  } finally {
    joining.value = false; //re enable button 
  }
}
//reloads the group data when the page first mounts 
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
            Login to join this group!
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
            No members yet.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>