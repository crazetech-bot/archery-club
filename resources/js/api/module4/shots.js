import axios from 'axios'

export const shotsApi = {

  create(scorecardId, payload) {
    return axios.post(`/api/scorecards/${scorecardId}/shots`, payload)
  },

  update(scorecardId, shotId, payload) {
    return axios.put(`/api/scorecards/${scorecardId}/shots/${shotId}`, payload)
  },

  delete(scorecardId, shotId) {
    return axios.delete(`/api/scorecards/${scorecardId}/shots/${shotId}`)
  },
}
