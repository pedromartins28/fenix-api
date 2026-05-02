<template>
  <q-page class="home-page">
    <section class="hero">
      <div class="hero__content">
        <p class="eyebrow">Fenix Challenge</p>
        <h1>Escolha como quer acessar o sistema</h1>
        <p class="subtitle">
          Ambiente simples para professores criarem provas e alunos responderem as avaliações.
        </p>

        <div class="role-grid">
          <q-card class="role-card" flat bordered>
            <q-card-section>
              <q-icon name="school" size="42px" color="primary" />
              <h2>Professor</h2>
              <p>Gerencie provas, questões, e acompanhe métricas.</p>
            </q-card-section>
            <q-card-actions>
              <q-btn color="primary" unelevated no-caps to="/teacher/exams" label="Entrar como professor" />
            </q-card-actions>
          </q-card>

          <q-card class="role-card" flat bordered>
            <q-card-section>
              <q-icon name="edit_note" size="42px" color="secondary" />
              <h2>Aluno</h2>
              <p>Veja provas disponíveis, inicie tentativas e acompanhe suas notas.</p>
            </q-card-section>
            <q-card-actions>
              <q-btn color="secondary" unelevated no-caps to="/student/exams" label="Entrar como aluno" />
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </section>
  </q-page>
</template>

<style scoped>
.home-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(38, 166, 154, 0.22), transparent 32rem),
    linear-gradient(135deg, #f6efe4 0%, #edf5ef 48%, #e6f0f7 100%);
}

.hero {
  min-height: calc(100vh - 50px);
  display: grid;
  place-items: center;
  padding: 48px 20px;
}

.hero__content {
  width: min(980px, 100%);
}

.eyebrow {
  margin: 0 0 10px;
  color: #5b6f68;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

h1 {
  max-width: 760px;
  margin: 0;
  color: #18221f;
  font-size: clamp(2.4rem, 7vw, 5rem);
  font-weight: 800;
  line-height: 0.95;
}

.subtitle {
  max-width: 620px;
  margin: 22px 0 34px;
  color: #4b5b56;
  font-size: 1.12rem;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.role-card {
  border-color: rgba(24, 34, 31, 0.12);
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.74);
  backdrop-filter: blur(16px);
}

.role-card h2 {
  margin: 18px 0 8px;
  color: #1d2a25;
  font-size: 1.5rem;
  font-weight: 800;
}

.role-card p {
  color: #56645f;
}

.role-card .q-card__actions {
  padding: 0 16px 18px;
}

@media (max-width: 720px) {
  .role-grid {
    grid-template-columns: 1fr;
  }
}
</style>
