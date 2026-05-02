<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" :to="`/student/exams/${$route.params.examId}`" />

    <div class="page-heading q-mt-md">
      <p class="eyebrow">Aluno</p>
      <h1>Responder prova #{{ $route.params.examId }}</h1>
      <p>Após iniciar, esta tela vai carregar questões e alternativas da tentativa.</p>
    </div>
  </q-page>
</template>

<style scoped>
.page-shell {
  background: #f7f4ec;
}

.eyebrow {
  color: #66736f;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
</style>
