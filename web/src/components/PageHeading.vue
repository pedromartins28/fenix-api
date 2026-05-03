<template>
  <div class="page-heading">
    <h1>{{ title }}</h1>
    <p v-if="subtitle" class="page-heading__subtitle">{{ subtitle }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.page-heading {
  max-width: 760px;
  margin-bottom: 24px;
}

h1 {
  margin: 0 0 10px;
  color: var(--app-text);
  font-size: clamp(2rem, 5vw, 3.4rem);
  font-weight: 800;
}

.page-heading__subtitle {
  margin: 0;
  color: var(--app-muted);
}
</style>
