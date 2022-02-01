const BASE_URL = "http://wordpressdemo.test/wp-json/passlesync/v1";
let API_KEY = "";
let PASSLE_SHORTCODE = "";

// TODO: Add apiKey
export const get = async (path) => {
  const url = BASE_URL + path;
  const response = await fetch(url);
  const result = await response.json();
  return result;
};

// TODO: Add apiKey
export const post = async (path, data) => {
  const url = BASE_URL + path;
  const response = await fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
    },
  });
  const result = await response.json();
  return result;
};

export const getWordPressPosts = async () => await get("/posts");
export const deleteWordPressPosts = async () => await get("/posts/delete");
export const getPostsFromPassleApi = async () => await get("/posts/api");
export const refreshPostsFromPassleApi = async () =>
  await get("/posts/api/update");
export const updatePost = async (data) => await post("/post/update", data);

export const setAPIKey = (apiKey) => (API_KEY = apiKey);
export const getAPIKey = () => API_KEY;
export const setPassleShortcode = (passleShortcode) => (PASSLE_SHORTCODE = passleShortcode);
export const getPassleShortcode = () => PASSLE_SHORTCODE;
