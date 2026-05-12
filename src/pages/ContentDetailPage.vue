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
const isLiked = ref(false);
const isFavourite = ref(false);

const message = ref("");
const commentMessage = ref("");
const replyMessage = ref("");
const replyTarget = ref(null);
const collapsedCommentIds = ref([]);

const isStaff = computed(() =>
  ["admin", "adminstaff"].includes(currentUser.value?.role)
);

const commentTree = computed(() => {
  const map = new Map();
  const roots = [];

  comments.value.forEach((comment) => {
    map.set(Number(comment.commentId), {
      ...comment,
      children: [],
    });
  });

  map.forEach((comment) => {
    const parentId = Number(comment.parentId || 0);

    if (parentId && map.has(parentId)) {
      map.get(parentId).children.push(comment);
    } else {
      roots.push(comment);
    }
  });

  return roots;
});

function setDefaultCollapsedReplies() {
  collapsedCommentIds.value = commentTree.value
    .filter((comment) => comment.children.length > 0)
    .map((comment) => Number(comment.commentId));
}

async function loadContent() {
  const result = await apiRequest(`contents.php?id=${contentId.value}`);
  content.value = result.data;
}

async function loadComments() {
  if (!isAuthenticated.value) {
    comments.value = [];
    collapsedCommentIds.value = [];
    return;
  }

  const result = await apiRequest(`comments.php?contentId=${contentId.value}`);
  comments.value = result.data || [];
  setDefaultCollapsedReplies();
}

async function loadLikes() {
  const result = await apiRequest(`likes.php?contentId=${contentId.value}`);
  likes.value = Number(result.data?.totalLikes || 0);
  isLiked.value = Boolean(result.data?.isLiked);
}

async function loadFavouriteStatus() {
  if (!isAuthenticated.value) {
    isFavourite.value = false;
    return;
  }

  const result = await apiRequest(`favourites.php?contentId=${contentId.value}`);
  isFavourite.value = Boolean(result.data?.isFavourite);
}

async function toggleLike() {
  try {
    message.value = "";

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
    message.value = "";

    await apiRequest("favourites.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
      }),
    });

    await loadFavouriteStatus();
  } catch (error) {
    message.value = error.message;
  }
}

async function addComment() {
  const text = commentMessage.value.trim();

  if (!text) return;

  try {
    message.value = "";

    await apiRequest("comments.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
        parentId: null,
        message: text,
      }),
    });

    commentMessage.value = "";
    await loadComments();
  } catch (error) {
    message.value = error.message;
  }
}

function startReply(comment) {
  replyTarget.value = comment;
  replyMessage.value = "";
}

function cancelReply() {
  replyTarget.value = null;
  replyMessage.value = "";
}

function isCommentCollapsed(commentId) {
  return collapsedCommentIds.value.includes(Number(commentId));
}

function toggleCommentReplies(commentId) {
  const id = Number(commentId);

  if (collapsedCommentIds.value.includes(id)) {
    collapsedCommentIds.value = collapsedCommentIds.value.filter(
      (item) => item !== id
    );
  } else {
    collapsedCommentIds.value.push(id);
  }
}

async function submitReply() {
  const text = replyMessage.value.trim();

  if (!text || !replyTarget.value) return;

  try {
    message.value = "";

    await apiRequest("comments.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: contentId.value,
        parentId: Number(replyTarget.value.commentId),
        message: text,
      }),
    });

    cancelReply();
    await loadComments();
  } catch (error) {
    message.value = error.message;
  }
}

async function deleteComment(commentId) {
  try {
    message.value = "";

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
    await loadLikes();
    await loadFavouriteStatus();
    await loadComments();
  } catch (error) {
    message.value = error.message;
  }
}

watch(contentId, loadAll);
watch(isAuthenticated, loadAll);

onMounted(loadAll);
</script>

<template>
  <section class="content-panel">
    <RouterLink to="/browse" class="back-link">
      <i class="bi bi-arrow-left"></i>
      Back to browse
    </RouterLink>

    <p v-if="message" class="alert alert-info mt-3">{{ message }}</p>

    <div v-if="content" class="mt-3">
      <article class="card surface-card overflow-hidden">
        <div class="detail-image-wrap">
          <img
            v-if="content.imageUrl"
            :src="content.imageUrl"
            class="detail-image"
            alt="content image"
          />

          <div v-if="isAuthenticated" class="floating-actions">
            <button
              class="icon-action like-action"
              :class="{ active: isLiked }"
              :title="isLiked ? 'Unlike' : 'Like'"
              type="button"
              @click="toggleLike"
            >
              <i :class="isLiked ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
            </button>

            <button
              class="icon-action favourite-action"
              :class="{ active: isFavourite }"
              :title="isFavourite ? 'Remove favourite' : 'Add favourite'"
              type="button"
              @click="toggleFavourite"
            >
              <i
                :class="
                  isFavourite ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'
                "
              ></i>
            </button>
          </div>
        </div>

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

          <p class="content-subtle mb-0">
            <i class="bi bi-heart-fill text-danger me-1"></i>
            {{ likes }} likes
          </p>

          <p class="detail-body">{{ content.body }}</p>

          <p v-if="!isAuthenticated" class="text-muted mt-4 mb-0">
            Login to like, favourite, and view comments.
          </p>
        </div>
      </article>
    </div>

    <div v-if="isAuthenticated" class="card surface-card mt-4">
      <div class="card-body p-4 p-lg-5">
        <p class="section-kicker">Discussion</p>
        <h3 class="section-title h4 mb-3">Comments</h3>

        <form @submit.prevent="addComment">
          <label class="form-label">Add a comment</label>
          <textarea
            v-model="commentMessage"
            class="form-control"
            rows="4"
            placeholder="Write your comment here"
          ></textarea>

          <button type="submit" class="btn btn-accent mt-3">
            Post comment
          </button>
        </form>

        <div v-if="commentTree.length" class="comment-stack mt-4">
          <article
            v-for="comment in commentTree"
            :key="comment.commentId"
            class="comment-card"
          >
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div class="flex-grow-1">
                <p class="comment-author mb-1">{{ comment.username }}</p>
                <p class="mb-2">{{ comment.message }}</p>

                <div class="comment-actions">
                  <button
                    class="comment-link-button"
                    type="button"
                    @click="startReply(comment)"
                  >
                    Reply
                  </button>

                  <button
                    v-if="comment.children.length"
                    class="comment-link-button"
                    type="button"
                    @click="toggleCommentReplies(comment.commentId)"
                  >
                    <span v-if="isCommentCollapsed(comment.commentId)">
                      Show {{ comment.children.length }}
                      {{ comment.children.length === 1 ? "reply" : "replies" }}
                    </span>

                    <span v-else>
                      Hide {{ comment.children.length }}
                      {{ comment.children.length === 1 ? "reply" : "replies" }}
                    </span>
                  </button>

                  <button
                    v-if="isStaff"
                    class="comment-link-button text-danger"
                    type="button"
                    @click="deleteComment(comment.commentId)"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>

            <div
              v-if="replyTarget?.commentId === comment.commentId"
              class="reply-box mt-3"
            >
              <p class="text-muted small mb-2">
                Replying to {{ replyTarget.username }}
              </p>

              <textarea
                v-model="replyMessage"
                class="form-control"
                rows="3"
                placeholder="Write your reply"
              ></textarea>

              <div class="d-flex gap-2 mt-2">
                <button
                  class="btn btn-sm btn-accent"
                  type="button"
                  @click="submitReply"
                >
                  Reply
                </button>

                <button
                  class="btn btn-sm btn-outline-dark"
                  type="button"
                  @click="cancelReply"
                >
                  Cancel
                </button>
              </div>
            </div>

            <div
              v-if="
                comment.children.length &&
                !isCommentCollapsed(comment.commentId)
              "
              class="reply-list"
            >
              <article
                v-for="reply in comment.children"
                :key="reply.commentId"
                class="reply-card"
              >
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="flex-grow-1">
                    <p class="comment-author mb-1">
                      {{ reply.username }}
                    </p>

                    <p v-if="reply.parentUsername" class="replying-label mb-1">
                      Replying to {{ reply.parentUsername }}
                    </p>

                    <p class="mb-2">{{ reply.message }}</p>

                    <div class="comment-actions">
                      <button
                        class="comment-link-button"
                        type="button"
                        @click="startReply(reply)"
                      >
                        Reply
                      </button>

                      <button
                        v-if="isStaff"
                        class="comment-link-button text-danger"
                        type="button"
                        @click="deleteComment(reply.commentId)"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>

                <div
                  v-if="replyTarget?.commentId === reply.commentId"
                  class="reply-box mt-3"
                >
                  <p class="text-muted small mb-2">
                    Replying to {{ replyTarget.username }}
                  </p>

                  <textarea
                    v-model="replyMessage"
                    class="form-control"
                    rows="3"
                    placeholder="Write your reply"
                  ></textarea>

                  <div class="d-flex gap-2 mt-2">
                    <button
                      class="btn btn-sm btn-accent"
                      type="button"
                      @click="submitReply"
                    >
                      Reply
                    </button>

                    <button
                      class="btn btn-sm btn-outline-dark"
                      type="button"
                      @click="cancelReply"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>
        </div>

        <p v-else class="text-muted mt-4 mb-0">
          No comments yet. Be the first one to comment.
        </p>
      </div>
    </div>

    <div v-else class="card surface-card mt-4">
      <div class="card-body p-4">
        <p class="mb-0 text-muted">
          Login to view and join the discussion.
        </p>
      </div>
    </div>
  </section>
</template>