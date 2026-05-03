<template>
  <q-layout view="lHh Lpr lFf">
    <q-header class="app-header">
      <q-toolbar>
        <q-toolbar-title class="brand" @click="$router.push('/')">
          <img class="brand__logo" src="/logo.png" alt="Fenix" />
        </q-toolbar-title>

        <q-tabs shrink stretch active-color="secondary" indicator-color="secondary">
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
  background: $primary;
}

.brand {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.brand__logo {
  display: block;
  width: auto;
  height: 34px;
  object-fit: contain;
}
</style>
