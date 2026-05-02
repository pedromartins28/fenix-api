const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

async function request (path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers
    },
    ...options
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Erro inesperado na API.' }))
    throw error
  }

  if (response.status === 204) {
    return null
  }

  return response.json()
}

export const api = {
  get: (path) => request(path),
  post: (path, body) => request(path, { method: 'POST', body: JSON.stringify(body) }),
  put: (path, body) => request(path, { method: 'PUT', body: JSON.stringify(body) }),
  delete: (path) => request(path, { method: 'DELETE' })
}

export const classGroupsApi = {
  list: () => api.get('/class-groups')
}

export const examsApi = {
  list: () => api.get('/exams'),
  show: (examId) => api.get(`/exams/${examId}`),
  create: (payload) => api.post('/exams', payload),
  update: (examId, payload) => api.put(`/exams/${examId}`, payload),
  delete: (examId) => api.delete(`/exams/${examId}`)
}
