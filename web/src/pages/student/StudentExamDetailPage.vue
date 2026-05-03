<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/student/exams" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <q-card v-else flat bordered class="detail-card q-mt-md">
      <q-inner-loading :showing="loading" />

      <q-card-section v-if="exam">
        <PageHeading
          :title="exam.name || `Prova #${exam.id}`"
          subtitle="Inicie a prova para visualizar as questões."
        />

        <div class="stats q-mt-lg">
          <div>
            <span>Questões</span>
            <strong>{{ exam.questions_count }}</strong>
          </div>
          <div>
            <span>Valor</span>
            <strong>{{ formatScore(exam.value) }}</strong>
          </div>
          <div>
            <span>Nota</span>
            <strong>{{ displayOrSlash(exam.score) }}</strong>
          </div>
        </div>
      </q-card-section>

      <q-card-actions class="detail-actions">
        <q-btn
          v-if="exam?.finished_at"
          color="primary"
          outline
          no-caps
          label="Visualizar"
          :to="`/student/results/${exam.id}`"
        />
        <q-btn
          v-else
          color="primary"
          unelevated
          no-caps
          label="Iniciar"
          :to="`/student/exams/${exam?.id}/attempt`"
        />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import PageHeading from 'src/components/PageHeading.vue'
import { STUDENT_ID } from 'src/config/app'
import { studentExamsApi } from 'src/services/api'

const route = useRoute()

const exam = ref(null)
const loading = ref(false)
const errorMessage = ref('')

onMounted(loadExam)

async function loadExam () {
  loading.value = true
  errorMessage.value = ''

  try {
    exam.value = await studentExamsApi.show(STUDENT_ID, route.params.examId)
  } catch (error) {
    errorMessage.value = error.message || 'Não foi possível carregar a prova.'
  } finally {
    loading.value = false
  }
}

function formatScore (value) {
  return Number(value).toFixed(2)
}

function displayOrSlash (value) {
  return value ?? '/'
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: var(--app-page);
}

.detail-card {
  width: min(760px, 100%);
  max-width: 760px;
  margin-inline: auto;
  border-radius: 22px;
  background: var(--app-surface);
  text-align: center;
}

.stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 12px;
}

.stats div {
  padding: 14px;
  border-radius: 16px;
  background: var(--app-surface-soft);
}

.stats span {
  display: block;
  color: var(--app-muted);
  font-size: 0.8rem;
}

.stats strong {
  color: var(--app-text);
  font-size: 1.15rem;
}

.detail-actions {
  justify-content: center;
  padding-bottom: 18px;
}
</style>
