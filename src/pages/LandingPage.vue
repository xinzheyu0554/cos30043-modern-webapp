<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { apiRequest } from "../api/client";
import { currentUser, isAuthenticated } from "../state/session";

const router = useRouter();

const carouselItems = ref([]);
const activeSlide = ref(0);
let slideshowTimer = null;

const currentSlide = computed(
  () => carouselItems.value[activeSlide.value] || null
);

const quickActions = computed(() => [
  {
    title: "Browse content",
    copy: "Search, sort, and open detailed item pages from the public collection.",
    to: "/browse",
    icon: "bi-grid-1x2",
  },
  {
    title: "Create an account",
    copy: "Register to unlock favourites, comments, likes, and profile tools.",
    to: isAuthenticated.value ? "/profile" : "/register",
    icon: "bi-person-plus",
  },
  {
    title: "About MyWay",
    copy: "Learn more about the platform, its purpose, and how students can share useful places and experiences.",
    to: "/about",
    icon: "bi-info-circle",
  },
]);

async function loadCarouselItems() {
  try {
    const result = await apiRequest("contents.php?limit=5&sort=newest");

    carouselItems.value = (result.data || [])
      .filter((item) => item.imageUrl)
      .slice(0, 5);
  } catch {
    carouselItems.value = [];
  }
}

function startSlideshow() {
  stopSlideshow();

  if (carouselItems.value.length <= 1) {
    return;
  }

  slideshowTimer = window.setInterval(() => {
    activeSlide.value =
      (activeSlide.value + 1) % Math.max(carouselItems.value.length, 1);
  }, 3800);
}

function stopSlideshow() {
  if (slideshowTimer) {
    window.clearInterval(slideshowTimer);
    slideshowTimer = null;
  }
}

function openAboutExperience() {
  router.push(isAuthenticated.value ? "/about" : "/login");
}

function goToSlide(index) {
  activeSlide.value = index;
  startSlideshow();
}

onMounted(async () => {
  await loadCarouselItems();
  startSlideshow();
});

onBeforeUnmount(stopSlideshow);
</script>

<template>
  <section class="content-panel">
    <div
      class="landing-carousel"
      :class="{ 'landing-carousel-empty': !currentSlide }"
      @click="openAboutExperience"
    >
      <div
        v-for="(item, index) in carouselItems"
        :key="item.contentId"
        class="landing-carousel-slide"
        :class="{ 'is-active': index === activeSlide }"
        :style="{ backgroundImage: `url(${item.imageUrl})` }"
      ></div>

      <div class="landing-carousel-overlay"></div>

      <div class="landing-carousel-content">
        <p class="landing-carousel-kicker">MyWay</p>

        <h2 class="landing-carousel-title">
          Explore stories, places, and student experiences
        </h2>

        <p class="landing-carousel-copy">
          Click the slideshow to learn more about the platform and how MyWay
          helps students discover and share memorable places.
        </p>

        <div v-if="currentSlide" class="landing-carousel-caption">
          <span class="content-chip">
            {{ currentSlide.category || "Featured" }}
          </span>

          <p class="landing-carousel-caption-title">
            {{ currentSlide.title }}
          </p>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
          <RouterLink
            :to="isAuthenticated ? '/browse' : '/login'"
            class="btn btn-accent"
            @click.stop
          >
            {{ isAuthenticated ? "Start browsing" : "Get started" }}
          </RouterLink>

          <RouterLink
            :to="isAuthenticated ? '/profile' : '/login'"
            class="btn btn-outline-light"
            @click.stop
          >
            {{ isAuthenticated ? "Open your account" : "Login" }}
          </RouterLink>
        </div>
      </div>

      <div
        v-if="carouselItems.length > 1"
        class="landing-carousel-dots"
        @click.stop
      >
        <button
          v-for="(item, index) in carouselItems"
          :key="item.contentId"
          type="button"
          class="landing-carousel-dot"
          :class="{ 'is-active': index === activeSlide }"
          :aria-label="`Go to slide ${index + 1}`"
          @click="goToSlide(index)"
        ></button>
      </div>
    </div>

    <div class="row g-4 mt-4">
      <div
        v-for="action in quickActions"
        :key="action.title"
        class="col-12 col-md-6 col-xl-4"
      >
        <RouterLink :to="action.to" class="card surface-card action-card h-100">
          <div class="card-body p-4">
            <i class="bi action-icon" :class="action.icon"></i>

            <h3 class="content-card-title mt-3">
              {{ action.title }}
            </h3>

            <p class="text-muted mb-0">
              {{ action.copy }}
            </p>
          </div>
        </RouterLink>
      </div>
    </div>
  </section>
</template>