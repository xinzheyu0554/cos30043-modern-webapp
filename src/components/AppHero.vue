<script setup>
defineProps({
  pageLabel: {
    type: String,
    default: "MyWay",
  },
});
</script>

<template>
  <section class="hero-band">
    <div class="container">
      <div class="row g-4">
        <div class="col-12">
          <h1 class="hero-title">{{ pageLabel }}</h1>
        </div>
      </div>
    </div>
  </section>
</template>
