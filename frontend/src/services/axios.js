import axios from "axios";

axios.interceptors.request.use(function (config) {
  config.baseURL = import.meta.env.VITE_API_URL;
  return config;
});

export default axios;
