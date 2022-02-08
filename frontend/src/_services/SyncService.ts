import {
  getWordPressPosts,
  deleteWordPressPosts,
  getPostsFromPassleApi,
  refreshPostsFromPassleApi,
} from "./APIService";

export type PasslePost = {
  PostShortcode: string;
  PostUrl: string;
  PostTitle: string;
  ContentTextSnippet: string;
  ImageUrl: string;
  PublishedDate: string;
  Authors: PassleAuthor[];
  syncState: string;
};

export type PassleAuthor = {
  Name: string;
  ProfileUrl: string;
};

export type WordpressPost = {
  ID: number;
  post_shortcode: string;
  post_image_html: string;
  post_date_gmt: string;
  post_image: string;
  post_title: string;
  guid: string;
  post_preview: string;
  post_authors: string[];
};

export const fetchSyncedPosts = async (finishLoadingCallback: () => void) => {
  const result = await getWordPressPosts();

  if (finishLoadingCallback) finishLoadingCallback();
  return result;
};

export const deleteSyncedPosts = async (finishLoadingCallback: () => void) => {
  await deleteWordPressPosts();
  let result = await fetchSyncedPosts(finishLoadingCallback);

  if (finishLoadingCallback) finishLoadingCallback();
  return result;
};

export const fetchUnsyncedPosts = async (finishLoadingCallback: () => void) => {
  let result = await getPostsFromPassleApi();

  if (finishLoadingCallback) finishLoadingCallback();
  return result;
};

export const updateUnsyncedPosts = async (
  finishLoadingCallback: () => void
) => {
  let result = await refreshPostsFromPassleApi();

  if (finishLoadingCallback) finishLoadingCallback();
  return result;
};
