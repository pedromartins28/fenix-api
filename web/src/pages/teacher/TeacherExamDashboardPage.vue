<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/teacher/exams" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <div class="page-heading q-mt-md">
      <p class="eyebrow">Professor</p>
      <h1>{{ dashboard?.exam?.name || `Dashboard da prova #${route.params.examId}` }}</h1>
      <p>Resumo de desempenho, melhor pontuação e ranking dos alunos.</p>
    </div>

    <q-card flat bordered class="filters-card">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-7">
            <q-select
              v-model="filters.class_group_id"
              outlined
              clearable
              emit-value
              map-options
              label="Filtrar por turma"
              option-label="code"
              option-value="id"
              :options="classGroups"
              :loading="loadingClassGroups"
              @update:model-value="goToFirstPage"
            />
          </div>
          <div class="col-12 col-md-5">
            <q-select
              v-model="filters.per_page"
              outlined
              emit-value
              map-options
              label="Itens por página"
              :options="perPageOptions"
              @update:model-value="goToFirstPage"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <div v-if="loadingDashboard" class="metrics-grid q-mt-lg">
      <q-skeleton v-for="item in 3" :key="item" height="132px" class="metric-card" />
    </div>

    <template v-else-if="dashboard">
      <div class="metrics-grid q-mt-lg">
        <q-card flat bordered class="metric-card">
          <q-card-section>
            <span>Média geral</span>
            <strong>{{ dashboard.average_score ?? 'null' }}</strong>
          </q-card-section>
        </q-card>

        <q-card flat bordered class="metric-card">
          <q-card-section>
            <span>Melhor pontuação</span>
            <strong>{{ dashboard.top_score ?? 'null' }}</strong>
          </q-card-section>
        </q-card>

        <q-card flat bordered class="metric-card">
          <q-card-section>
            <span>Top 1</span>
            <strong>{{ dashboard.top_attempt?.student?.name || 'null' }}</strong>
            <small v-if="dashboard.top_attempt">
              {{ dashboard.top_attempt.correct_answers_count }} acertos
            </small>
          </q-card-section>
        </q-card>
      </div>

      <q-card flat bordered class="ranking-card q-mt-lg">
        <q-card-section>
          <div class="row items-start justify-between q-gutter-md">
            <div>
              <div class="text-h6 text-weight-bold">Ranking</div>
              <p class="text-body2 text-grey-7 q-mb-none">
                Página {{ ranking.current_page || 1 }} de {{ ranking.last_page || 1 }}
              </p>
            </div>

            <q-chip outline color="primary">
              {{ ranking.total || 0 }} finalizações
            </q-chip>
          </div>
        </q-card-section>

        <q-table
          flat
          :rows="ranking.data || []"
          :columns="columns"
          row-key="attempt_id"
          hide-pagination
          :rows-per-page-options="[0]"
        >
          <template #body-cell-position="props">
            <q-td :props="props">
              #{{ ranking.from + props.rowIndex }}
            </q-td>
          </template>

          <template #body-cell-student="props">
            <q-td :props="props">
              <div class="text-weight-bold">{{ props.row.student.name }}</div>
              <div class="text-caption text-grey-7">{{ props.row.student.class_group.code }}</div>
            </q-td>
          </template>
        </q-table>

        <q-card-actions align="right">
          <q-pagination
            v-model="filters.page"
            :max="ranking.last_page || 1"
            direction-links
            boundary-links
            color="primary"
          />
        </q-card-actions>
      </q-card>

      <div class="text-caption text-grey-7 q-mt-sm">
        Atualizado em: {{ formatDate(dashboard.requested_at) }}
      </div>
    </template>
  </q-page>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { classGroupsApi, examDashboardApi } from 'src/services/api'

const route = useRoute()

const classGroups = ref([])
const dashboard = ref(null)
const loadingClassGroups = ref(false)
const loadingDashboard = ref(false)
const errorMessage = ref('')

const filters = reactive({
  class_group_id: null,
  per_page: 10,
  page: 1
})

const perPageOptions = [
  { label: '5 por página', value: 5 },
  { label: '10 por página', value: 10 },
  { label: '15 por página', value: 15 }
]

const ranking = computed(() => dashboard.value?.ranking || {})

const columns = [
  { name: 'position', label: '#', field: 'attempt_id', align: 'left' },
  { name: 'student', label: 'Aluno', field: 'student', align: 'left' },
  { name: 'correct_answers_count', label: 'Acertos', field: 'correct_answers_count', align: 'center' },
  { name: 'score', label: 'Nota', field: 'score', align: 'center' },
  { name: 'finished_at', label: 'Finalizada em', field: 'finished_at', align: 'left', format: formatDate }
]

onMounted(async () => {
  await Promise.all([loadClassGroups(), loadDashboard()])
})

watch(
  () => filters.page,
  () => {
    loadDashboard()
  }
)

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

async function loadDashboard () {
  loadingDashboard.value = true
  errorMessage.value = ''

  try {
    dashboard.value = await examDashboardApi.show(route.params.examId, filters)
  } catch (error) {
    dashboard.value = null
    errorMessage.value = error.message || 'Não foi possível carregar a dashboard.'
  } finally {
    loadingDashboard.value = false
  }
}

function goToFirstPage () {
  if (filters.page === 1) {
    loadDashboard()
    return
  }

  filters.page = 1
}

function formatDate (value) {
  if (!value) {
    return 'null'
  }

  return new Date(value).toLocaleString('pt-BR')
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: #f5f1e8;
}

.page-heading {
  max-width: 860px;
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

.filters-card,
.ranking-card,
.metric-card {
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.86);
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.metric-card span {
  display: block;
  color: #6b7773;
  font-size: 0.86rem;
}

.metric-card strong {
  display: block;
  margin-top: 8px;
  color: #17231f;
  font-size: clamp(1.7rem, 4vw, 2.5rem);
  font-weight: 800;
}

.metric-card small {
  color: #6b7773;
}
</style>
