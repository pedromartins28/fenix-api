<template>
  <q-page padding class="page-shell">
    <div class="row items-start justify-between q-gutter-md">
      <div class="page-heading">
        <p class="eyebrow">Professor</p>
        <h1>Provas cadastradas</h1>
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
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { classGroupsApi } from 'src/services/api'

const classGroups = ref([])
const selectedClassGroupId = ref(null)
const loadingClassGroups = ref(false)

onMounted(async () => {
  loadingClassGroups.value = true

  try {
    classGroups.value = await classGroupsApi.list()
  } catch {
    classGroups.value = []
  } finally {
    loadingClassGroups.value = false
  }
})
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
</style>
