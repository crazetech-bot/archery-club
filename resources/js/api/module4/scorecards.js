import axios from 'axios'

export const scorecardsApi = {

  list() {
    return axios.get('/api/scorecards')
  },

  get(id) {
    return axios.get(`/api/scorecards/${id}`)
  },

  create(payload) {
    return axios.post('/api/scorecards', payload)
  },

  update(id, payload) {
    return axios.put(`/api/scorecards/${id}`, payload)
  },

  delete(id) {
    return axios.delete(`/api/scorecards/${id}`)
  },

  submit(id) {
    return axios.post(`/api/scorecards/${id}/submit`)
  },

  lock(id) {
    return axios.post(`/api/scorecards/${id}/lock`)
  },
}
