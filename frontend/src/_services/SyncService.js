import {
    getWordPressPosts,
    deleteWordPressPosts
  } from "./APIService";
  
export const fetchSyncedPosts = async (finishLoadingCallback) => {
    const result = await getWordPressPosts();
    if (finishLoadingCallback) finishLoadingCallback();
    return result;
};

export const deleteSyncedPosts = async (finishLoadingCallback) => {
    const result = await deleteWordPressPosts();
    await fetchSyncedPosts(finishLoadingCallback);
};