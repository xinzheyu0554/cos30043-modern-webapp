<script setup>
import { ref, onMounted, watch } from "vue";
import { apiRequest } from "../api/client";

const props = defineProps({
  contentId: Number,
  user: Object,
});

const emit = defineEmits(["back"]);

const content = ref(null);
const comments = ref([]);
const likes = ref(0);
const message = ref("");
const commentMessage = ref("");

async function loadContent() {
  const result = await apiRequest(`contents.php?id=${props.contentId}`);
  content.value = result.data;
}

async function loadComments() {
  const result = await apiRequest(`comments.php?contentId=${props.contentId}`);
  comments.value = result.data || [];
}

async function loadLikes() {
  const result = await apiRequest(`likes.php?contentId=${props.contentId}`);
  likes.value = result.data?.totalLikes || 0;
}

async function toggleLike() {
  try {
    await apiRequest("likes.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: props.contentId,
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
        contentId: props.contentId,
      }),
    });

    message.value = "Favourite updated";
  } catch (error) {
    message.value = error.message;
  }
}

async function addComment() {
  try {
    await apiRequest("comments.php", {
      method: "POST",
      body: JSON.stringify({
        contentId: props.contentId,
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
  if (!props.contentId) return;

  try {
    message.value = "";
    await loadContent();
    await loadComments();
    await loadLikes();
  } catch (error) {
    message.value = error.message;
  }
}

watch(() => props.contentId, loadAll);
onMounted(loadAll);
</script>

<template>
  <div>
    <button @click="emit('back')">Back</button>

    <p>{{ message }}</p>

    <div v-if="content">
      <h2>{{ content.title }}</h2>
      <p>Author: {{ content.author }}</p>
      <p>Category: {{ content.category }}</p>
      <p>Creator: {{ content.creatorName }}</p>

      <img v-if="content.imageUrl" :src="content.imageUrl" alt="content image" width="200" />

      <p>{{ content.body }}</p>

      <p>Likes: {{ likes }}</p>

      <button v-if="user" @click="toggleLike">Like / Unlike</button>
      <button v-if="user" @click="toggleFavourite">Favourite / Unfavourite</button>
    </div>

    <hr />

    <h3>Comments</h3>

    <form v-if="user" @submit.prevent="addComment">
      <textarea v-model="commentMessage" placeholder="Write a comment"></textarea>
      <br />
      <button type="submit">Post Comment</button>
    </form>

    <p v-else>Please login to comment.</p>

    <div v-for="comment in comments" :key="comment.commentId">
      <p>{{ comment.username }}: {{ comment.message }}</p>

      <button
        v-if="user && (user.role === 'admin' || user.role === 'adminstaff')"
        @click="deleteComment(comment.commentId)"
      >
        Delete Comment
      </button>

      <hr />
    </div>
  </div>
</template>