<template>
  <q-page padding class="page-shell">
    <div class="row items-start justify-between q-gutter-md">
      <div class="page-heading">
        <p class="eyebrow">Professor</p>
        <h1>Provas cadastradas</h1>
        <p>Crie, edite, exclua e acompanhe as provas do sistema.</p>
      </div>

      <q-btn color="primary" unelevated no-caps icon="add" label="Criar prova" to="/teacher/exams/create" />
    </div>

    <q-card flat bordered class="filter-card">
      <q-card-section>
        <div class="text-subtitle1 text-weight-bold">Filtro por turma</div>
        <q-select
          v-model="selectedClassGroupId"
          outlined
          clearable
          emit-value
          map-options
          label="Turma"
          option-label="code"
          option-value="id"
          :options="classGroups"
          :loading="loadingClassGroups"
        />
      </q-card-section>
    </q-card>

    <div v-if="loadingExams" class="exam-grid q-mt-lg">
      <q-skeleton v-for="item in 3" :key="item" height="190px" class="exam-card" />
    </div>

    <q-banner v-else-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-lg">
      {{ errorMessage }}
    </q-banner>

    <div v-else-if="filteredExams.length === 0" class="empty-state q-mt-lg">
      <q-icon name="assignment" size="44px" />
      <h2>Nenhuma prova encontrada</h2>
      <p>Crie uma prova para começar a organizar as avaliações.</p>
    </div>

    <div v-else class="exam-grid q-mt-lg">
      <q-card v-for="exam in filteredExams" :key="exam.id" flat bordered class="exam-card">
        <q-card-section>
          <div class="text-caption text-grey-7">Prova #{{ exam.id }}</div>
          <h2>{{ exam.name || `Prova ${exam.id}` }}</h2>

          <div class="stats q-mt-md">
            <div>
              <span>Questões</span>
              <strong>{{ exam.questions_count }}</strong>
            </div>
            <div>
              <span>Valor</span>
              <strong>{{ formatScore(exam.value) }}</strong>
            </div>
          </div>

          <div class="q-mt-md">
            <q-chip
              v-for="classGroup in exam.class_groups"
              :key="classGroup.id"
              dense
              outline
              color="primary"
            >
              {{ classGroup.code }}
            </q-chip>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat dense no-caps color="primary" label="Dashboard" :to="`/teacher/exams/${exam.id}/dashboard`" />
          <q-btn flat dense no-caps color="secondary" label="Turmas" :to="`/teacher/exams/${exam.id}/classes`" />
          <q-btn flat dense no-caps color="primary" label="Editar" :to="`/teacher/exams/${exam.id}/edit`" />
          <q-btn flat dense no-caps color="negative" label="Excluir" @click="deleteExam(exam)" />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { classGroupsApi, examsApi } from 'src/services/api'

const $q = useQuasar()

const exams = ref([])
const classGroups = ref([])
const selectedClassGroupId = ref(null)
const loadingClassGroups = ref(false)
const loadingExams = ref(false)
const errorMessage = ref('')

const filteredExams = computed(() => {
  if (!selectedClassGroupId.value) {
    return exams.value
  }

  return exams.value.filter((exam) => {
    return exam.class_groups?.some((classGroup) => classGroup.id === selectedClassGroupId.value)
  })
})

onMounted(async () => {
  await Promise.all([loadClassGroups(), loadExams()])
})

async function loadClassGroups () {
  loadingClassGroups.value = true

  try {
    classGroups.value = await classGroupsApi.list()
  } catch {
    classGroups.value = []
  } finally {
    loadingClassGroups.value = false
  }
}

async function loadExams () {
  loadingExams.value = true
  errorMessage.value = ''

  try {
    exams.value = await examsApi.list()
  } catch (error) {
    errorMessage.value = error.message || 'Não foi possível carregar as provas.'
  } finally {
    loadingExams.value = false
  }
}

async function deleteExam (exam) {
  const confirmed = window.confirm(`Excluir a prova "${exam.name || exam.id}"?`)

  if (!confirmed) {
    return
  }

  try {
    await examsApi.delete(exam.id)
    $q.notify({ type: 'positive', message: 'Prova excluída com sucesso.' })
    await loadExams()
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message || 'Não foi possível excluir a prova.' })
  }
}

function formatScore (value) {
  return Number(value).toFixed(2)
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f5f1e8;
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

.filter-card {
  max-width: 520px;
  border-radius: 18px;
}

.exam-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap: 18px;
}

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

.stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
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
