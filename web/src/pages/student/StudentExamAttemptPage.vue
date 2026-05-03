<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" :to="`/student/exams/${route.params.examId}`" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <div v-if="loading" class="q-mt-lg">
      <q-skeleton height="90px" class="q-mb-md" />
      <q-skeleton v-for="item in 3" :key="item" height="180px" class="q-mb-md" />
    </div>

    <div v-else-if="attempt" class="attempt-shell q-mt-md">
      <div class="page-heading">
        <p class="eyebrow">Tentativa #{{ attempt.id }}</p>
        <h1>{{ attempt.exam.name || `Prova #${attempt.exam.id}` }}</h1>
        <p>Responda todas as questões antes de finalizar a prova.</p>
      </div>

      <q-form class="question-stack" @submit.prevent="submitAnswers">
        <q-card
          v-for="question in attempt.exam.questions"
          :key="question.id"
          flat
          bordered
          class="question-card"
        >
          <q-card-section>
            <div class="text-caption text-grey-7">Questão {{ question.position }}</div>
            <h2>{{ question.statement }}</h2>

            <q-option-group
              v-model="selectedOptions[question.id]"
              :options="question.options.map((option) => ({
                label: option.description,
                value: option.id
              }))"
              color="primary"
              type="radio"
            />
          </q-card-section>
        </q-card>

        <div class="row justify-end q-gutter-sm">
          <q-btn flat no-caps label="Voltar depois" to="/student/exams" />
          <q-btn color="primary" unelevated no-caps type="submit" label="Finalizar prova" :loading="submitting" />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import { examAttemptsApi, studentExamsApi } from 'src/services/api'

const STUDENT_ID = 1
const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const attempt = ref(null)
const loading = ref(false)
const submitting = ref(false)
const errorMessage = ref('')
const selectedOptions = reactive({})

onMounted(loadOrStartAttempt)

async function loadOrStartAttempt () {
  loading.value = true
  errorMessage.value = ''

  try {
    attempt.value = await studentExamsApi.showAttempt(STUDENT_ID, route.params.examId)
  } catch {
    try {
      attempt.value = await studentExamsApi.startAttempt(STUDENT_ID, route.params.examId)
    } catch (error) {
      errorMessage.value = error.message || 'Não foi possível iniciar a prova.'
    }
  } finally {
    loading.value = false
  }

  if (attempt.value?.finished_at) {
    router.replace(`/student/results/${route.params.examId}`)
  }
}

async function submitAnswers () {
  const questions = attempt.value.exam.questions
  const missingAnswer = questions.some((question) => !selectedOptions[question.id])

  if (missingAnswer) {
    $q.notify({ type: 'warning', message: 'Responda todas as questões antes de finalizar.' })
    return
  }

  submitting.value = true

  try {
    const answers = questions.map((question) => ({
      exam_question_id: question.id,
      exam_question_option_id: selectedOptions[question.id]
    }))

    await examAttemptsApi.submitAnswers(attempt.value.id, answers)
    $q.notify({ type: 'positive', message: 'Prova enviada com sucesso.' })
    router.push(`/student/results/${route.params.examId}`)
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message || 'Não foi possível enviar a prova.' })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f7f4ec;
}

.attempt-shell {
  max-width: 900px;
}

.page-heading {
  margin-bottom: 24px;
}

.eyebrow {
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

.question-stack {
  display: grid;
  gap: 16px;
}

.question-card {
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.86);
}

.question-card h2 {
  margin: 4px 0 16px;
  color: #17231f;
  font-size: 1.25rem;
  font-weight: 800;
}
</style>
