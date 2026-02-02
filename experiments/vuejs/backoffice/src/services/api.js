import axios from 'axios'

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  transformRequest: [function (data, headers) {
    if (data && typeof data === 'object' && !(data instanceof FormData)) {
        const params = new URLSearchParams();
        for (const key in data) {
            params.append(key, data[key]);
        }
        return params;
    }
    return data;
  }],
})

export default apiClient
