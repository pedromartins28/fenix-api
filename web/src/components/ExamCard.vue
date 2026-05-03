<template>
  <q-card flat bordered class="exam-card">
    <q-card-section>
      <div class="row items-start justify-between q-gutter-sm">
        <div>
          <h2>{{ exam.name || `Prova ${exam.id}` }}</h2>
        </div>

        <slot name="badge" />
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

    <q-card-actions v-if="$slots.actions" align="right">
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
  background: rgba(255, 255, 255, 0.84);
}

.exam-card h2 {
  margin: 4px 0 0;
  color: #17231f;
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
  background: #f5f1e8;
}

.exam-card__stats span {
  display: block;
  color: #6b7773;
  font-size: 0.78rem;
}

.exam-card__stats strong {
  color: #17231f;
  font-size: 1.08rem;
}
</style>
