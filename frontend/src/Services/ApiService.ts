import { PasslePost } from "_Services/SyncService";

const BASE_URL: string = "http://wordpressdemo.test/wp-json/passlesync/v1";
let API_KEY: string = "";

export const get = async (path: string) => {
  const url = BASE_URL + path;
  const response = await fetch(url, {
    headers: {
      APIKey: API_KEY,
    },
  });

  let text = "";
  try {
    text = await response.text();
    return JSON.parse(text);
  } catch (err) {
    console.log(err);
    return text;
  }
};

export const post = async (path: string, data?: object) => {
  const url = BASE_URL + path;
  const response = await fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      APIKey: API_KEY,
    },
  });

  let text = "";
  try {
    text = await response.text();
    return JSON.parse(text);
  } catch (err) {
    console.log(err);
    return text;
  }
};

export const getAllPosts = async () => await get("/posts");
export const deleteWordPressPosts = async () => await post("/posts/delete");
export const refreshPostsFromPassleApi = async () =>
  await get("/posts/refresh");
export const updatePost = async (data: PasslePost) =>
  await post("/post/update", data);
export const updateAllPosts = async (data: object) =>
  await post("/posts/update", data);

export const setAPIKey = (apiKey: string) => (API_KEY = apiKey);
export const getAPIKey = () => API_KEY;
export const updateSettings = async (data: object) =>
  await post("/settings/update", data);
