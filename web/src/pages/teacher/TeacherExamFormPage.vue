<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/teacher/exams" />

    <PageHeading
      class="q-mt-md"
      eyebrow="Professor"
      :title="isEditing ? 'Editar prova' : 'Criar prova'"
      subtitle="Cadastre os dados da prova e monte as questões."
    />

    <q-form class="exam-form" @submit.prevent="submitForm">
      <q-inner-loading :showing="loadingExam" />

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
              <div class="text-h6 text-weight-bold">Questões</div>
              <p class="text-body2 text-grey-7 q-mb-none">Adicione as questões e marque uma alternativa correta em cada uma.</p>
            </div>

            <q-btn flat no-caps color="primary" icon="add" label="Adicionar questão" @click="addQuestion" />
          </div>

          <q-list separator class="q-mt-md">
            <q-expansion-item
              v-for="(question, questionIndex) in form.questions"
              :key="questionIndex"
              expand-separator
              :label="`Questão ${question.position}`"
              default-opened
            >
              <div class="q-pa-md">
                <div class="row justify-end q-mb-sm">
                  <q-btn
                    flat
                    dense
                    no-caps
                    color="negative"
                    icon="delete"
                    label="Remover questão"
                    :disable="form.questions.length <= 1"
                    @click="removeQuestion(questionIndex)"
                  />
                </div>

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
                  <q-btn
                    flat
                    round
                    dense
                    color="negative"
                    icon="delete"
                    :disable="question.options.length <= 2"
                    @click="removeOption(question, optionIndex)"
                  >
                    <q-tooltip>Remover alternativa</q-tooltip>
                  </q-btn>
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
import PageHeading from 'src/components/PageHeading.vue'
import { examsApi } from 'src/services/api'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const isEditing = computed(() => Boolean(route.params.examId))
const saving = ref(false)
const loadingExam = ref(false)

const form = reactive({
  name: '',
  questions_count: 1,
  value: 10,
  questions: [createQuestion(1)]
})

onMounted(async () => {
  if (isEditing.value) {
    await loadExam()
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

function addQuestion () {
  form.questions.push(createQuestion(form.questions.length + 1))
  syncQuestionsPositions()
}

function removeQuestion (questionIndex) {
  if (form.questions.length <= 1) {
    return
  }

  form.questions.splice(questionIndex, 1)
  syncQuestionsPositions()
}

function syncQuestionsPositions () {
  form.questions_count = form.questions.length
  form.questions.forEach((question, index) => {
    question.position = index + 1
  })
}

function addOption (question) {
  question.options.push({ description: '', is_correct: false })
}

function removeOption (question, optionIndex) {
  if (question.options.length <= 2) {
    return
  }

  const wasCorrect = question.options[optionIndex].is_correct
  question.options.splice(optionIndex, 1)

  if (wasCorrect || correctOptionIndex(question) === -1) {
    question.options[0].is_correct = true
  }
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
    const payload = buildPayload()

    if (isEditing.value) {
      await examsApi.update(route.params.examId, payload)
    } else {
      await examsApi.create(payload)
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

async function loadExam () {
  loadingExam.value = true

  try {
    const exam = await examsApi.show(route.params.examId)
    form.name = exam.name || ''
    form.questions_count = Number(exam.questions_count)
    form.value = Number(exam.value)
    form.questions = exam.questions.map((question, index) => ({
      statement: question.statement,
      position: question.position || index + 1,
      options: question.options.map((option) => ({
        description: option.description,
        is_correct: Boolean(option.is_correct)
      }))
    }))
    syncQuestionsPositions()
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message || 'Não foi possível carregar a prova.' })
    router.push('/teacher/exams')
  } finally {
    loadingExam.value = false
  }
}

function buildPayload () {
  syncQuestionsPositions()

  return {
    name: form.name,
    questions_count: form.questions_count,
    value: form.value,
    questions: form.questions.map((question) => ({
      statement: question.statement,
      position: question.position,
      options: question.options.map((option) => ({
        description: option.description,
        is_correct: option.is_correct
      }))
    }))
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

.option-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto auto;
  gap: 12px;
  align-items: center;
  margin-bottom: 10px;
}

@media (max-width: 680px) {
  .option-row {
    grid-template-columns: 1fr;
  }
}
</style>
