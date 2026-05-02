<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/student/exams" />

    <div class="page-heading q-mt-md">
      <p class="eyebrow">Aluno</p>
      <h1>Detalhe da prova #{{ $route.params.examId }}</h1>
      <p>Resumo da prova antes de iniciar a tentativa.</p>
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
