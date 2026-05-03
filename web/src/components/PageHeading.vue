<template>
  <div class="page-heading">
    <p v-if="eyebrow" class="page-heading__eyebrow">{{ eyebrow }}</p>
    <h1>{{ title }}</h1>
    <p v-if="subtitle" class="page-heading__subtitle">{{ subtitle }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  eyebrow: {
    type: String,
    default: ''
  },
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

.page-heading__eyebrow {
  margin: 0 0 8px;
  color: #66736f;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

h1 {
  margin: 0 0 10px;
  color: #17231f;
  font-size: clamp(2rem, 5vw, 3.4rem);
  font-weight: 800;
}

.page-heading__subtitle {
  margin: 0;
  color: #586761;
}
</style>
