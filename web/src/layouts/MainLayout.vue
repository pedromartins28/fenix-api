<template>
  <q-layout view="lHh Lpr lFf">
    <q-header class="app-header">
      <q-toolbar>
        <q-toolbar-title class="brand" @click="$router.push('/')">
          Fenix
        </q-toolbar-title>

        <q-tabs shrink stretch>
          <q-route-tab to="/teacher/exams" label="Professor" />
          <q-route-tab to="/student/exams" label="Aluno" />
        </q-tabs>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<style scoped>
.app-header {
  background: #18221f;
}

.brand {
  cursor: pointer;
  font-weight: 800;
  letter-spacing: 0.04em;
}
</style>
