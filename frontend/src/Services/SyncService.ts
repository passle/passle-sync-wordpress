import { getAllPosts, refreshPostsFromPassleApi } from "./APIService";
import { SyncState } from "_API/Enums/SyncState";

export const fetchPosts = async (finishLoadingCallback: () => void) => {
  const result = await getAllPosts();

  if (finishLoadingCallback) finishLoadingCallback();
  return result;
};

export const updateUnsyncedPosts = async () => {
  await refreshPostsFromPassleApi();
};
