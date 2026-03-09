import axios from 'axios'

export const endsApi = {

  create(scorecardId, payload) {
    return axios.post(`/api/scorecards/${scorecardId}/ends`, payload)
  },

  update(scorecardId, endId, payload) {
    return axios.put(`/api/scorecards/${scorecardId}/ends/${endId}`, payload)
  },

  delete(scorecardId, endId) {
    return axios.delete(`/api/scorecards/${scorecardId}/ends/${endId}`)
  },
}
