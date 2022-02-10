import { getAllPosts, refreshPostsFromPassleApi } from "./APIService";
import { SyncState } from "_API/Enums/SyncState";

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
