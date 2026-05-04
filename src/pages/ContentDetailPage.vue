<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { RouterLink, useRoute } from "vue-router";
import { apiRequest } from "../api/client";
import { currentUser, isAuthenticated } from "../state/session";

const route = useRoute();
const contentId = computed(() => Number(route.params.id));
const content = ref(null);
const comments = ref([]);
const likes = ref(0);
const message = ref("");
const commentMessage = ref("");

async function loadContent() {
  const result = await apiRequest(`contents.php?id=${contentId.value}`);
  content.value = result.data;
}

async function loadComments() {
  const result = await apiRequest(`comments.php?contentId=${contentId.value}`);
  comments.value = result.data || [];
}

async function loadLikes() {
  const result = await apiRequest(`likes.php?contentId=${contentId.value}`);
  likes.value = result.data?.totalLikes || 0;
}

async function toggleLike() {
  try {
    await apiRequest("likes.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
      }),
    });

    await loadLikes();
  } catch (error) {
    message.value = error.message;
  }
}

async function toggleFavourite() {
  try {
    await apiRequest("favourites.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
      }),
    });

    message.value = "Favourite list updated.";
  } catch (error) {
    message.value = error.message;
  }
}

async function addComment() {
  try {
    await apiRequest("comments.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
        message: commentMessage.value,
      }),
    });

    commentMessage.value = "";
    await loadComments();
  } catch (error) {
    message.value = error.message;
  }
}

async function deleteComment(commentId) {
  try {
    await apiRequest("comments.php", {
      method: "DELETE",
      body: JSON.stringify({
        commentId,
      }),
    });

    await loadComments();
  } catch (error) {
    message.value = error.message;
  }
}

async function loadAll() {
  if (!contentId.value) return;

  try {
    message.value = "";
    await loadContent();
    await loadComments();
    await loadLikes();
  } catch (error) {
    message.value = error.message;
  }
}

watch(contentId, loadAll);
onMounted(loadAll);
</script>

<template>
  <section class="content-panel">
    <RouterLink to="/browse" class="back-link">
      <i class="bi bi-arrow-left"></i>
      Back to browse
    </RouterLink>

    <p v-if="message" class="alert alert-info mt-3">{{ message }}</p>

    <div v-if="content" class="row g-4 mt-1">
      <div class="col-12 col-lg-8">
        <article class="card surface-card h-100">
          <img
            v-if="content.imageUrl"
            :src="content.imageUrl"
            class="detail-image"
            alt="content image"
          />
          <div class="card-body p-4 p-lg-5">
            <div class="content-meta-row mb-3">
              <span class="content-chip">{{ content.category || "General" }}</span>
              <span class="content-subtle">
                Added by {{ content.creatorName || "Staff" }}
              </span>
            </div>
            <h2 class="section-title">{{ content.title }}</h2>
            <p class="content-subtle">
              Author: {{ content.author || "Unknown" }}
            </p>
            <p class="detail-body">{{ content.body }}</p>
          </div>
        </article>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card surface-card h-100">
          <div class="card-body p-4">
            <p class="section-kicker">Social actions</p>
            <h3 class="info-title">Community engagement</h3>
            <p class="mb-3">Likes: {{ likes }}</p>

            <div class="d-grid gap-2">
              <button
                v-if="isAuthenticated"
                class="btn btn-accent"
                @click="toggleLike"
              >
                Like or unlike
              </button>
              <button
                v-if="isAuthenticated"
                class="btn btn-outline-dark"
                @click="toggleFavourite"
              >
                Toggle favourite
              </button>
            </div>

            <p v-if="!isAuthenticated" class="text-muted mt-3 mb-0">
              Login to like, favourite, and comment.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="card surface-card mt-4">
      <div class="card-body p-4 p-lg-5">
        <p class="section-kicker">Discussion</p>
        <h3 class="section-title h4 mb-3">Comments</h3>

        <form v-if="isAuthenticated" @submit.prevent="addComment">
          <label class="form-label">Add a comment</label>
          <textarea
            v-model="commentMessage"
            class="form-control"
            rows="4"
            placeholder="Write your comment here"
          ></textarea>
          <button type="submit" class="btn btn-accent mt-3">Post comment</button>
        </form>

        <p v-else class="text-muted mb-0">Please login to join the discussion.</p>

        <div v-if="comments.length" class="comment-stack mt-4">
          <article
            v-for="comment in comments"
            :key="comment.commentId"
            class="comment-card"
          >
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <p class="comment-author mb-1">{{ comment.username }}</p>
                <p class="mb-0">{{ comment.message }}</p>
              </div>

              <button
                v-if="['admin', 'adminstaff'].includes(currentUser?.role)"
                class="btn btn-sm btn-outline-danger"
                @click="deleteComment(comment.commentId)"
              >
                Delete
              </button>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
