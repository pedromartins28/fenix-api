<template>
  <q-page class="home-page">
    <section class="hero">
      <div class="hero__content">
        <h1>Escolha como quer acessar o sistema</h1>
        <p class="subtitle">
          Ambiente simples para professores criarem provas e alunos responderem as avaliações.
        </p>

        <div class="role-grid">
          <q-card class="role-card" flat bordered>
            <q-card-section>
              <q-icon name="school" size="42px" color="secondary" />
              <h2>Professor</h2>
              <p>Gerencie provas, questões, e acompanhe métricas.</p>
            </q-card-section>
            <q-card-actions class="role-card__actions">
              <q-btn color="secondary" unelevated no-caps to="/teacher/exams" label="Entrar como professor" />
            </q-card-actions>
          </q-card>

          <q-card class="role-card" flat bordered>
            <q-card-section>
              <q-icon name="edit_note" size="42px" color="secondary" />
              <h2>Aluno</h2>
              <p>Veja provas disponíveis, inicie tentativas e acompanhe suas notas.</p>
            </q-card-section>
            <q-card-actions class="role-card__actions">
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
    radial-gradient(circle at top right, rgba(228, 84, 36, 0.28), transparent 32%),
    radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.12), transparent 30%),
    #071a2b;
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

h1 {
  max-width: 760px;
  margin: 0;
  color: #ffffff;
  font-size: clamp(2.4rem, 7vw, 5rem);
  font-weight: 800;
  line-height: 0.95;
}

.subtitle {
  max-width: 620px;
  margin: 22px 0 34px;
  color: rgba(255, 255, 255, 0.78);
  font-size: 1.12rem;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.role-card {
  border: 1px solid rgba(255, 255, 255, 0.72);
  border-radius: 24px;
  background: #071a2b;
  backdrop-filter: blur(16px);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.24);
}

.role-card h2 {
  margin: 18px 0 8px;
  color: #ffffff;
  font-size: 1.5rem;
  font-weight: 800;
}

.role-card p {
  color: rgba(255, 255, 255, 0.74);
}

.role-card__actions {
  padding: 0 16px 18px;
}

@media (max-width: 720px) {
  .role-grid {
    grid-template-columns: 1fr;
  }
}
</style>
