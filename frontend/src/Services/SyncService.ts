import {
  getAllPosts,
  refreshPostsFromPassleApi,
} from "./APIService";
import { SyncState } from "_API/Enums/SyncState";

export type PasslePost = {
  PostShortcode: string;
  PostUrl: string;
  PostTitle: string;
  ContentTextSnippet: string;
  ImageUrl: string;
  PublishedDate: string;
  Authors: PassleAuthor[];
  SyncState: SyncState;
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
  post_authors: string;
};

export const fetchPosts = async (finishLoadingCallback: () => void) => {
  const result = await getAllPosts();

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
