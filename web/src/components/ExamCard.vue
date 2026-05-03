<template>
  <q-card flat bordered class="exam-card">
    <q-card-section class="exam-card__section">
      <div v-if="$slots.badge" class="exam-card__badge">
        <slot name="badge" />
      </div>

      <div class="exam-card__header">
        <h2>{{ exam.name }}</h2>
      </div>

      <slot name="stats">
        <div class="exam-card__stats">
          <div>
            <span>Questões</span>
            <strong>{{ exam.questions_count }}</strong>
          </div>
          <div>
            <span>Valor</span>
            <strong>{{ formatScore(exam.value) }}</strong>
          </div>
        </div>
      </slot>

      <slot name="content" />
    </q-card-section>

    <q-card-actions v-if="$slots.actions" class="exam-card__actions">
      <slot name="actions" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
defineProps({
  exam: {
    type: Object,
    required: true
  }
})

function formatScore (value) {
  return Number(value).toFixed(2)
}
</script>

<style scoped>
.exam-card {
  width: 100%;
  border-radius: 20px;
  background: var(--app-surface);
}

.exam-card__section {
  position: relative;
  display: flex;
  min-height: 190px;
  flex-direction: column;
  justify-content: center;
  padding-top: 46px;
}

.exam-card__header {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.exam-card__badge {
  position: absolute;
  top: 16px;
  right: 16px;
}

.exam-card h2 {
  margin: 4px 0 0;
  color: var(--app-text);
  font-size: 1.35rem;
  font-weight: 800;
}

.exam-card__stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  margin-top: 18px;
}

.exam-card__stats div {
  padding: 12px;
  border-radius: 14px;
  background: var(--app-surface-soft);
}

.exam-card__stats span {
  display: block;
  color: var(--app-muted);
  font-size: 0.78rem;
}

.exam-card__stats strong {
  color: var(--app-text);
  font-size: 1.08rem;
}

.exam-card__actions {
  justify-content: center;
  padding-bottom: 16px;
}
</style>
