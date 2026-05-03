<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/teacher/exams" />

    <q-banner v-if="errorMessage" rounded class="bg-red-1 text-red-10 q-mt-md">
      {{ errorMessage }}
    </q-banner>

    <q-card flat bordered class="classes-card q-mt-md">
      <q-inner-loading :showing="loading" />

      <q-card-section>
        <PageHeading
          title="Turmas da prova"
          :subtitle="exam ? exam.name || `Prova #${exam.id}` : `Prova #${route.params.examId}`"
        />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="text-h6 text-weight-bold">Turmas disponíveis</div>
        <p class="text-body2 text-grey-7">
          Marque quais turmas terão acesso a esta prova e salve as alterações.
        </p>

        <div class="class-grid q-mt-md">
          <q-checkbox
            v-for="classGroup in classGroups"
            :key="classGroup.id"
            v-model="selectedClassGroupIds"
            :val="classGroup.id"
            :label="classGroup.code"
            color="primary"
          />
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat no-caps label="Cancelar" to="/teacher/exams" />
        <q-btn color="primary" unelevated no-caps label="Salvar" :loading="saving" @click="saveLinks" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import PageHeading from 'src/components/PageHeading.vue'
import { classGroupExamsApi, classGroupsApi, examsApi } from 'src/services/api'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const exam = ref(null)
const classGroups = ref([])
const selectedClassGroupIds = ref([])
const initialClassGroupIds = ref([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')

onMounted(loadPage)

async function loadPage () {
  loading.value = true
  errorMessage.value = ''

  try {
    const [examResponse, classGroupsResponse] = await Promise.all([
      examsApi.show(route.params.examId),
      classGroupsApi.list()
    ])

    exam.value = examResponse
    classGroups.value = classGroupsResponse
    initialClassGroupIds.value = examResponse.class_groups?.map((classGroup) => classGroup.id) || []
    selectedClassGroupIds.value = [...initialClassGroupIds.value]
  } catch (error) {
    errorMessage.value = error.message || 'Não foi possível carregar as turmas da prova.'
  } finally {
    loading.value = false
  }
}

async function saveLinks () {
  saving.value = true

  const selected = new Set(selectedClassGroupIds.value)
  const initial = new Set(initialClassGroupIds.value)
  const toLink = [...selected].filter((classGroupId) => !initial.has(classGroupId))
  const toUnlink = [...initial].filter((classGroupId) => !selected.has(classGroupId))

  try {
    await Promise.all([
      ...toLink.map((classGroupId) => classGroupExamsApi.link(classGroupId, route.params.examId)),
      ...toUnlink.map((classGroupId) => classGroupExamsApi.unlink(classGroupId, route.params.examId))
    ])

    $q.notify({ type: 'positive', message: 'Vínculos atualizados com sucesso.', position: 'top' })
    router.push('/teacher/exams')
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message || 'Não foi possível atualizar os vínculos.', position: 'top' })
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.page-shell {
  min-height: calc(100vh - 50px);
  background: var(--app-page);
}

.classes-card {
  max-width: 760px;
  border-radius: 22px;
  background: var(--app-surface);
}

.class-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 8px;
}
</style>
