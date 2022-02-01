import { useState, useEffect } from "react";
import {
  getPostsFromPassleApi,
  refreshPostsFromPassleApi,
  updatePost,
} from "../../_services/APIService";
import UnsyncedPost from "./_components/UnsyncedPost/UnsyncedPost";
import LoadingButton from "../LoadingButton";
import PaginatedItems from "../Pagination/Pagination";
import "./UnsyncedPosts.css";

function UnsyncedPosts({ syncCallback, syncedPosts }) {
  const [posts, setPosts] = useState([]);
  const [unsyncedPosts, setUnsyncedPosts] = useState([]);

  useEffect(() => {
    async function initialFetch() {
      await fetchUnsyncedPosts(null);
    }
    initialFetch();
  }, []);

  useEffect(() => {
    // Set this as an effect whenever posts or syncedPosts changes
    // This catches any API post updates, but also handles the race condition on page load
    // where the synced and unsynced posts are loaded at the same time
    const unsynced = dedupePosts(posts);
    setUnsyncedPosts(unsynced);
  }, [posts, syncedPosts]);

  const fetchUnsyncedPosts = async (finishLoadingCallback) => {
    let result = await getPostsFromPassleApi();

    if (finishLoadingCallback) finishLoadingCallback();
    setPosts(result);
  };

  const updateUnsyncedPosts = async (finishLoadingCallback) => {
    let result = await refreshPostsFromPassleApi();

    if (finishLoadingCallback) finishLoadingCallback();
    setPosts(result);
  };

  const dedupePosts = (unsyncedPosts) => {
    const syncedShortcodes = syncedPosts.map((p) => p["post_shortcode"]);
    return unsyncedPosts.filter(
      (p) => !syncedShortcodes.includes(p["PostShortcode"])
    );
  };

  const syncAll = async (finishLoadingCallback) => {
    let promises = [];
    unsyncedPosts.forEach((post, i) => {
      promises.push(syncPost(post, null));
    });

    Promise.all(promises).then(() => {
      if (finishLoadingCallback) finishLoadingCallback();
      syncCallback();
    });
  };

  const syncPost = async (post, finishLoadingCallback) => {
    post = { ...post, syncState: "syncing" };

    const result = await updatePost(post);
    if (finishLoadingCallback) finishLoadingCallback();

    post = { ...post, syncState: "synced" };
    if (finishLoadingCallback) syncCallback();
  };

  return (
    <>
      <h2>{unsyncedPosts?.length ?? 0} Unsynced Passle Posts:</h2>

      <LoadingButton
        className="update-list"
        text={"Update list"}
        callback={updateUnsyncedPosts}
        loadingText={"Fetching posts..."}
      />

      {unsyncedPosts && unsyncedPosts.length > 0 && (
        <LoadingButton
          className="sync-all"
          text={"Sync all"}
          callback={syncAll}
          loadingText={"Syncing posts..."}
        />
      )}

      {unsyncedPosts &&
        unsyncedPosts.length > 0 &&
        <PaginatedItems 
          items={unsyncedPosts} 
          renderItem={(post) => <UnsyncedPost post={post} key={post.PostShortcode} syncPost={syncPost} syncState={post.syncState}/>}
        />
        }

      {(!posts || !posts.length) && <h3>No posts fetched</h3>}
      {posts &&
        posts.length > 0 &&
        (!unsyncedPosts || !unsyncedPosts.length) && (
          <h3>All posts have been synced</h3>
        )}
    </>
  );
}

export default UnsyncedPosts;
