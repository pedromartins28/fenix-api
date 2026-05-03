<template>
  <q-page padding class="page-shell">
    <PageHeading
      title="Provas disponíveis"
      :subtitle="`Aluno #${STUDENT_ID}`"
    />

    <div v-if="loading" class="row q-gutter-md">
      <q-skeleton v-for="item in 3" :key="item" height="180px" class="exam-card" />
    </div>

    <q-banner v-else-if="errorMessage" rounded class="bg-red-1 text-red-10">
      {{ errorMessage }}
    </q-banner>

    <div v-else-if="exams.length === 0" class="empty-state">
      <q-icon name="assignment" size="46px" />
      <h2>Nenhuma prova disponível</h2>
      <p>Quando uma prova for vinculada à sua turma, ela aparecerá aqui.</p>
    </div>

    <div v-else class="exam-grid">
      <ExamCard v-for="exam in exams" :key="exam.id" :exam="exam">
        <template #badge>
          <q-chip :color="statusMeta(exam).color" text-color="white" dense>
            {{ statusMeta(exam).label }}
          </q-chip>
        </template>

        <template #stats>
          <div class="stats">
            <div>
              <span>Valor</span>
              <strong>{{ formatScore(exam.value) }}</strong>
            </div>
            <div>
              <span>Nota</span>
              <strong>{{ displayOrSlash(exam.score) }}</strong>
            </div>
            <div>
              <span>Percentual</span>
              <strong>{{ accuracyLabel(exam) }}</strong>
            </div>
          </div>
        </template>

        <template #actions>
          <q-btn
            v-if="!exam.started_at"
            color="primary"
            unelevated
            no-caps
            label="Iniciar"
            :to="`/student/exams/${exam.id}`"
          />
          <q-btn
            v-else-if="!exam.finished_at"
            color="secondary"
            unelevated
            no-caps
            label="Continuar"
            :to="`/student/exams/${exam.id}/attempt`"
          />
          <q-btn
            v-else
            color="primary"
            outline
            no-caps
            label="Visualizar"
            :to="`/student/results/${exam.id}`"
          />
        </template>
      </ExamCard>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import ExamCard from 'src/components/ExamCard.vue'
import PageHeading from 'src/components/PageHeading.vue'
import { STUDENT_ID } from 'src/config/app'
import { studentExamsApi } from 'src/services/api'

const exams = ref([])
const loading = ref(false)
const errorMessage = ref('')

onMounted(loadExams)

async function loadExams () {
  loading.value = true
  errorMessage.value = ''

  try {
    exams.value = await studentExamsApi.list(STUDENT_ID)
  } catch (error) {
    errorMessage.value = error.message || 'Não foi possível carregar as provas disponíveis.'
  } finally {
    loading.value = false
  }
}

function statusMeta (exam) {
  if (exam.finished_at) {
    return { label: 'Finalizada', color: 'positive' }
  }

  if (exam.started_at) {
    return { label: 'Em Andamento', color: 'warning' }
  }

  return { label: 'Não Iniciada', color: 'grey-7' }
}

function accuracyLabel (exam) {
  if (!exam.finished_at || exam.correct_answers_count === null) {
    return '/'
  }

  const percentage = (Number(exam.correct_answers_count) / Number(exam.questions_count)) * 100

  return `${percentage.toFixed(0)}%`
}

function displayOrSlash (value) {
  return value ?? '/'
}

function formatScore (value) {
  return Number(value).toFixed(2)
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: var(--app-page);
}

.exam-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 18px;
}

.exam-card {
  width: 100%;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 22px;
}

.stats div {
  padding: 12px;
  border-radius: 14px;
  background: var(--app-surface-soft);
}

.stats span {
  display: block;
  color: var(--app-muted);
  font-size: 0.78rem;
}

.stats strong {
  color: var(--app-text);
  font-size: 1.08rem;
}

.empty-state {
  max-width: 460px;
  padding: 32px;
  border-radius: 22px;
  background: var(--app-surface);
  color: var(--app-muted);
}
</style>
