import axios from "axios";

let API_KEY: string = "";

const instance = axios.create({
  baseURL: "http://wordpressdemo.test/wp-json/passlesync/v1",
});

export const get = async <T>(path: string, params?: object) => {
  const response = await instance.get<T>(path, {
    headers: { APIKey: API_KEY },
    params,
  });

  return response.data;
};

export const post = async <T>(path: string, data?: object) => {
  const response = await instance.post<T>(path, data, {
    headers: { APIKey: API_KEY },
  });

  return response.data;
};

export const setAPIKey = (apiKey: string) => (API_KEY = apiKey);
export const getAPIKey = () => API_KEY;
