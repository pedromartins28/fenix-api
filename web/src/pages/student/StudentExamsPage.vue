<template>
  <q-page padding class="page-shell">
    <div class="page-heading">
      <p class="eyebrow">Aluno</p>
      <h1>Provas disponíveis</h1>
      <p>Aluno fixo de teste: <strong>#{{ STUDENT_ID }}</strong></p>
    </div>

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
      <q-card v-for="exam in exams" :key="exam.id" flat bordered class="exam-card">
        <q-card-section>
          <div class="row items-start justify-between q-gutter-sm">
            <div>
              <div class="text-caption text-grey-7">Prova #{{ exam.id }}</div>
              <h2>{{ exam.name || `Prova ${exam.id}` }}</h2>
            </div>

            <q-chip :color="statusMeta(exam).color" text-color="white" dense>
              {{ statusMeta(exam).label }}
            </q-chip>
          </div>

          <div class="stats">
            <div>
              <span>Valor</span>
              <strong>{{ formatScore(exam.value) }}</strong>
            </div>
            <div>
              <span>Nota</span>
              <strong>{{ exam.score ?? 'null' }}</strong>
            </div>
            <div>
              <span>Acerto</span>
              <strong>{{ accuracyLabel(exam) }}</strong>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
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
            label="Ver resultado"
            :to="`/student/results/${exam.id}`"
          />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
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
    return { label: 'finalizada', color: 'positive' }
  }

  if (exam.started_at) {
    return { label: 'em andamento', color: 'warning' }
  }

  return { label: 'não iniciada', color: 'grey-7' }
}

function accuracyLabel (exam) {
  if (!exam.finished_at || exam.correct_answers_count === null) {
    return 'null'
  }

  const percentage = (Number(exam.correct_answers_count) / Number(exam.questions_count)) * 100

  return `${percentage.toFixed(0)}%`
}

function formatScore (value) {
  return Number(value).toFixed(2)
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f7f4ec;
}

.page-heading {
  max-width: 760px;
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

.exam-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 18px;
}

.exam-card {
  width: 100%;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.82);
}

.exam-card h2 {
  margin: 4px 0 0;
  font-size: 1.35rem;
  font-weight: 800;
  color: #17231f;
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
  background: #f5f1e8;
}

.stats span {
  display: block;
  color: #6b7773;
  font-size: 0.78rem;
}

.stats strong {
  color: #17231f;
  font-size: 1.08rem;
}

.empty-state {
  max-width: 460px;
  padding: 32px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.76);
  color: #56645f;
}
</style>
