<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/teacher/exams" />

    <div class="page-heading q-mt-md">
      <p class="eyebrow">Professor</p>
      <h1>{{ isEditing ? 'Editar prova' : 'Criar prova' }}</h1>
      <p>Cadastre os dados da prova, monte as questões e selecione as turmas com acesso.</p>
    </div>

    <q-form class="exam-form" @submit.prevent="submitForm">
      <q-card flat bordered class="form-card">
        <q-card-section>
          <div class="text-h6 text-weight-bold">Dados da prova</div>
          <div class="row q-col-gutter-md q-mt-sm">
            <div class="col-12 col-md-6">
              <q-input v-model="form.name" outlined label="Nome da prova" />
            </div>
            <div class="col-12 col-md-3">
              <q-input
                v-model.number="form.questions_count"
                outlined
                type="number"
                min="1"
                label="Número de questões"
                @update:model-value="syncQuestionsCount"
              />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model.number="form.value" outlined type="number" min="0" step="0.01" label="Valor" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <q-card flat bordered class="form-card">
        <q-card-section>
          <div class="row items-center justify-between q-gutter-md">
            <div>
              <div class="text-h6 text-weight-bold">Turmas com acesso</div>
              <p class="text-body2 text-grey-7 q-mb-none">Selecione uma ou mais turmas para publicar a prova.</p>
            </div>
            <q-spinner v-if="loadingClassGroups" color="primary" size="24px" />
          </div>

          <div class="class-grid q-mt-md">
            <q-checkbox
              v-for="classGroup in classGroups"
              :key="classGroup.id"
              v-model="form.class_group_ids"
              :val="classGroup.id"
              :label="classGroup.code"
              color="primary"
            />
          </div>
        </q-card-section>
      </q-card>

      <q-card flat bordered class="form-card">
        <q-card-section>
          <div class="text-h6 text-weight-bold">Questões</div>

          <q-list separator class="q-mt-md">
            <q-expansion-item
              v-for="(question, questionIndex) in form.questions"
              :key="questionIndex"
              expand-separator
              :label="`Questão ${question.position}`"
              default-opened
            >
              <div class="q-pa-md">
                <q-input v-model="question.statement" outlined type="textarea" label="Enunciado" />

                <div class="text-subtitle2 text-weight-bold q-mt-lg q-mb-sm">Alternativas</div>
                <div
                  v-for="(option, optionIndex) in question.options"
                  :key="optionIndex"
                  class="option-row"
                >
                  <q-input v-model="option.description" outlined dense label="Descrição da alternativa" />
                  <q-radio
                    :model-value="correctOptionIndex(question)"
                    :val="optionIndex"
                    label="Correta"
                    color="primary"
                    @update:model-value="markCorrect(question, optionIndex)"
                  />
                </div>

                <q-btn
                  flat
                  no-caps
                  color="primary"
                  icon="add"
                  label="Adicionar alternativa"
                  class="q-mt-sm"
                  @click="addOption(question)"
                />
              </div>
            </q-expansion-item>
          </q-list>
        </q-card-section>
      </q-card>

      <div class="row justify-end q-gutter-sm">
        <q-btn flat no-caps label="Cancelar" to="/teacher/exams" />
        <q-btn color="primary" unelevated no-caps type="submit" label="Salvar prova" :loading="saving" />
      </div>
    </q-form>
  </q-page>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import { classGroupsApi, examsApi } from 'src/services/api'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const isEditing = computed(() => Boolean(route.params.examId))
const classGroups = ref([])
const loadingClassGroups = ref(false)
const saving = ref(false)

const form = reactive({
  name: '',
  questions_count: 1,
  value: 10,
  class_group_ids: [],
  questions: [createQuestion(1)]
})

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

function createQuestion (position) {
  return {
    statement: '',
    position,
    options: [
      { description: '', is_correct: true },
      { description: '', is_correct: false }
    ]
  }
}

function syncQuestionsCount () {
  const count = Math.max(Number(form.questions_count) || 1, 1)
  form.questions_count = count

  while (form.questions.length < count) {
    form.questions.push(createQuestion(form.questions.length + 1))
  }

  while (form.questions.length > count) {
    form.questions.pop()
  }

  form.questions.forEach((question, index) => {
    question.position = index + 1
  })
}

function addOption (question) {
  question.options.push({ description: '', is_correct: false })
}

function correctOptionIndex (question) {
  return question.options.findIndex((option) => option.is_correct)
}

function markCorrect (question, selectedIndex) {
  question.options.forEach((option, index) => {
    option.is_correct = index === selectedIndex
  })
}

async function submitForm () {
  saving.value = true

  try {
    if (isEditing.value) {
      await examsApi.update(route.params.examId, form)
    } else {
      await examsApi.create(form)
    }

    $q.notify({ type: 'positive', message: 'Prova salva com sucesso.' })
    router.push('/teacher/exams')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.message || 'Não foi possível salvar a prova.'
    })
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.page-shell {
  background: #f5f1e8;
}

.exam-form {
  display: grid;
  gap: 18px;
  max-width: 980px;
}

.form-card {
  border-radius: 18px;
}

.class-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 8px;
}

.option-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  margin-bottom: 10px;
}

.eyebrow {
  color: #66736f;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

@media (max-width: 680px) {
  .option-row {
    grid-template-columns: 1fr;
  }
}
</style>
