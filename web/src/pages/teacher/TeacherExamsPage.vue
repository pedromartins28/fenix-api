<template>
  <q-page padding class="page-shell">
    <div class="row items-start justify-between q-gutter-md">
      <PageHeading
        eyebrow="Professor"
        title="Provas cadastradas"
        subtitle="Crie, edite, exclua e acompanhe as provas do sistema."
      />

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
      <ExamCard v-for="exam in filteredExams" :key="exam.id" :exam="exam">
        <template #content>
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
        </template>

        <template #actions>
          <q-btn flat dense no-caps color="primary" label="Métricas" :to="`/teacher/exams/${exam.id}/dashboard`" />
          <q-btn flat dense no-caps color="secondary" label="Turmas" :to="`/teacher/exams/${exam.id}/classes`" />
          <q-btn flat dense no-caps color="primary" label="Editar" :to="`/teacher/exams/${exam.id}/edit`" />
          <q-btn flat dense no-caps color="negative" label="Excluir" @click="deleteExam(exam)" />
        </template>
      </ExamCard>
    </div>
  </q-page>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import ExamCard from 'src/components/ExamCard.vue'
import PageHeading from 'src/components/PageHeading.vue'
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

</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f5f1e8;
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
}

.empty-state {
  max-width: 460px;
  padding: 32px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.76);
  color: #56645f;
}
</style>
