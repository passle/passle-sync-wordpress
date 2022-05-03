import axios from "axios";

let API_KEY: string = "";

const passleSyncSettingsPageRoot = document.getElementById(
  "passle-sync-settings-root",
);

const instance = axios.create({
  baseURL: "/wp-json/passlesync/v1",
  headers: {
    "X-WP-Nonce": passleSyncSettingsPageRoot.dataset.wpNonce,
  },
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
