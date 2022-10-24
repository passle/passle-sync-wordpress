import axios from "axios";

const passleSyncSettingsPageRoot = document.getElementById(
  "passle-sync-settings-root",
);

let baseUrl: string;

try {
  baseUrl = JSON.parse(
    passleSyncSettingsPageRoot?.dataset.passlesyncOptions,
  ).siteUrl;
} catch {}

const instance = axios.create({
  baseURL: baseUrl + "/wp-json/passlesync/v1",
  headers: {
    "X-WP-Nonce": passleSyncSettingsPageRoot?.dataset.wpNonce ?? "",
  },
});

export const get = async <T>(path: string, params?: object) => {
  const response = await instance.get<T>(path, {
    params,
  });

  return response.data;
};

export const post = async <T>(path: string, data?: object) => {
  const response = await instance.post<T>(path, data);

  return response.data;
};
