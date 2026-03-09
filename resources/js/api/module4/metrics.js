import axios from 'axios'

export const metricsApi = {

  recalculate(scorecardId) {
    return axios.post(`/api/scorecards/${scorecardId}/metrics/recalculate`)
  },
}
