<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar para provas" to="/student/exams" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <q-card v-else flat bordered class="result-card q-mt-md">
      <q-inner-loading :showing="loading" />

      <q-card-section v-if="exam">
        <p class="eyebrow">Resultado</p>
        <h1>{{ exam.name || `Prova #${exam.id}` }}</h1>

        <div class="score-panel q-mt-lg">
          <div>
            <span>Nota</span>
            <strong>{{ exam.score ?? 'null' }}</strong>
          </div>
          <div>
            <span>Acertos</span>
            <strong>{{ exam.correct_answers_count ?? 'null' }} / {{ exam.questions_count }}</strong>
          </div>
          <div>
            <span>Aproveitamento</span>
            <strong>{{ accuracyLabel }}</strong>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { studentExamsApi } from 'src/services/api'

const STUDENT_ID = 1
const route = useRoute()

const exam = ref(null)
const loading = ref(false)
const errorMessage = ref('')

const accuracyLabel = computed(() => {
  if (!exam.value?.finished_at || exam.value.correct_answers_count === null) {
    return 'null'
  }

  const percentage = (Number(exam.value.correct_answers_count) / Number(exam.value.questions_count)) * 100

  return `${percentage.toFixed(0)}%`
})

onMounted(loadResult)

async function loadResult () {
  loading.value = true
  errorMessage.value = ''

  try {
    exam.value = await studentExamsApi.show(STUDENT_ID, route.params.examId)
  } catch (error) {
    errorMessage.value = error.message || 'Não foi possível carregar o resultado.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f7f4ec;
}

.result-card {
  max-width: 820px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.86);
}

.eyebrow {
  margin: 0 0 8px;
  color: #66736f;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  color: #17231f;
  font-size: clamp(2rem, 5vw, 3.4rem);
  font-weight: 800;
}

.score-panel {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 14px;
}

.score-panel div {
  padding: 18px;
  border-radius: 18px;
  background: #f5f1e8;
}

.score-panel span {
  display: block;
  color: #6b7773;
}

.score-panel strong {
  color: #17231f;
  font-size: 1.6rem;
}
</style>
