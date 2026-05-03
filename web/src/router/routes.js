const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/HomePage.vue') },
      { path: 'student', redirect: '/student/exams' },
      { path: 'student/exams', component: () => import('pages/student/StudentExamsPage.vue') },
      { path: 'student/exams/:examId', component: () => import('pages/student/StudentExamDetailPage.vue') },
      {
        path: 'student/exams/:examId/attempt',
        component: () => import('pages/student/StudentExamAttemptPage.vue')
      },
      { path: 'student/results/:examId', component: () => import('pages/student/StudentExamResultPage.vue') },
      { path: 'teacher', redirect: '/teacher/exams' },
      { path: 'teacher/exams', component: () => import('pages/teacher/TeacherExamsPage.vue') },
      { path: 'teacher/exams/create', component: () => import('pages/teacher/TeacherExamFormPage.vue') },
      { path: 'teacher/exams/:examId/edit', component: () => import('pages/teacher/TeacherExamFormPage.vue') },
      { path: 'teacher/exams/:examId/classes', component: () => import('pages/teacher/TeacherExamClassesPage.vue') },
      { path: 'teacher/exams/:examId/dashboard', component: () => import('pages/teacher/TeacherExamDashboardPage.vue') }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
