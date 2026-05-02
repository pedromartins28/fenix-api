<template>
  <q-page padding class="page-shell">
    <q-btn flat no-caps icon="arrow_back" label="Voltar" to="/teacher/exams" />

    <div class="page-heading q-mt-md">
      <p class="eyebrow">Professor</p>
      <h1>Dashboard da prova #{{ $route.params.examId }}</h1>
    </div>
  </q-page>
</template>

<style scoped>
.page-shell {
  background: #f5f1e8;
}

.eyebrow {
  color: #66736f;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
</style>
