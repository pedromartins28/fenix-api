<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/student/exams" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <div v-if="loading" class="loading-shell q-mt-lg">
      <q-skeleton height="90px" class="q-mb-md" />
      <q-skeleton v-for="item in 3" :key="item" height="180px" class="q-mb-md" />
    </div>

    <div v-else-if="attempt" class="attempt-shell q-mt-md">
      <PageHeading
        :title="attempt.exam.name || `Prova #${attempt.exam.id}`"
        subtitle="Responda todas as questões antes de finalizar a prova."
      />

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
          <q-btn color="primary" unelevated no-caps type="submit" label="Finalizar" :loading="submitting" />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import PageHeading from 'src/components/PageHeading.vue'
import { STUDENT_ID } from 'src/config/app'
import { examAttemptsApi, studentExamsApi } from 'src/services/api'

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
    $q.notify({ type: 'warning', message: 'Responda todas as questões antes de finalizar.', position: 'top' })
    return
  }

  submitting.value = true

  try {
    const answers = questions.map((question) => ({
      exam_question_id: question.id,
      exam_question_option_id: selectedOptions[question.id]
    }))

    await examAttemptsApi.submitAnswers(attempt.value.id, answers)
    $q.notify({ type: 'positive', message: 'Prova enviada com sucesso.', position: 'top' })
    router.push(`/student/results/${route.params.examId}`)
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message || 'Não foi possível enviar a prova.', position: 'top' })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: var(--app-page);
}

.attempt-shell {
  width: min(900px, 100%);
  max-width: 900px;
  margin: 0 auto;
  text-align: center;
}

.loading-shell {
  width: min(900px, 100%);
  margin-inline: auto;
}

.question-stack {
  display: grid;
  gap: 16px;
}

.question-card {
  border-radius: 20px;
  background: var(--app-surface);
  text-align: left;
}

.question-card h2 {
  margin: 4px 0 16px;
  color: var(--app-text);
  font-size: 1.25rem;
  font-weight: 800;
}
</style>
