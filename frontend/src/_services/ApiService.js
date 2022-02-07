const BASE_URL = "http://wordpressdemo.test/wp-json/passlesync/v1";
let API_KEY = "";

export const get = async (path) => {
  const url = BASE_URL + path;
  const response = await fetch(url, {
    headers: {
      "APIKey": API_KEY
    }
  });

  let text = '';
  try {
    text = await response.text();
    return JSON.parse(text);
  } catch (err) {
    console.error(err);
    return text;
  }
};

export const post = async (path, data) => {
  const url = BASE_URL + path;
  const response = await fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      "APIKey": API_KEY
    },
  });

  let text = '';
  try {
    text = await response.text();
    return JSON.parse(text);
  } catch (err) {
    console.error(err);
    return text;
  }
};

export const getWordPressPosts = async () => await get("/posts");
export const deleteWordPressPosts = async () => await post("/posts/delete");
export const getPostsFromPassleApi = async () => await get("/posts/api");
export const refreshPostsFromPassleApi = async () =>
  await get("/posts/api/update");
export const updatePost = async (data) => await post("/post/update", data);
export const syncAllPosts = async (data) => await post("/posts/api/sync", data);
export const checkSyncProgress = async () => await get("/posts/api/sync/progress");

export const setAPIKey = (apiKey) => (API_KEY = apiKey);
export const getAPIKey = () => API_KEY;
export const updateSettings = async (data) => await post("/settings/update", data);
