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
    await deleteWordPressPosts();
    let result = await fetchSyncedPosts(finishLoadingCallback);
    if (finishLoadingCallback) finishLoadingCallback();
    return result;
};