import { useState, useEffect, useRef } from "react";
import {
  getPostsFromPassleApi,
  refreshPostsFromPassleApi,
  updatePost,
  syncAllPosts,
  checkSyncProgress
} from "../../_services/APIService";
import UnsyncedPost from "./_components/UnsyncedPost/UnsyncedPost";
import LoadingButton from "../LoadingButton";
import PaginatedItems from "../Pagination/Pagination";
import "./UnsyncedPosts.css";

function UnsyncedPosts({ syncCallback, syncedPosts }) {
  const [posts, setPosts] = useState([]);
  const [unsyncedPosts, setUnsyncedPosts] = useState([]);
  const [totalSyncItems, setTotalSyncItems] = useState(0);
  const [completedSyncItems, setCompletedSyncItems] = useState(0);
  let pollInterval = useRef();

  useEffect(() => {
    async function initialFetch() {
      await fetchUnsyncedPosts(null);
    }
    async function fetchSyncProgress() {
      let syncDetails = await checkSyncProgress();
      setTotalSyncItems(parseInt(syncDetails.total));
      setCompletedSyncItems(parseInt(syncDetails.done));
      if (parseInt(syncDetails.total) > parseInt(syncDetails.done)) {
        pollInterval.current = setInterval(() => {
          checkProgress(null);
        }, 1000);
      }
    }
    initialFetch();
    fetchSyncProgress();
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
    
    const syncedShortcodes = Array.isArray(syncedPosts) ? syncedPosts.map((p) => p["post_shortcode"]) : [];
    console.log(syncedPosts, unsyncedPosts);
    return (Array.isArray(unsyncedPosts)) ? unsyncedPosts.filter(
      (p) => !syncedShortcodes.includes(p["PostShortcode"])
    ) : [];
  };

  const syncAll = async (finishLoadingCallback) => {
    syncAllPosts(unsyncedPosts);
    setTotalSyncItems(unsyncedPosts.length);
    pollInterval.current = setInterval(() => {
      checkProgress(finishLoadingCallback)
    }, 1000);
  };

  const checkProgress = async (finishLoadingCallback) => {
    checkSyncProgress().then(({total, done}) => {
      if (done === total ) {
        if (finishLoadingCallback) finishLoadingCallback();
        clearInterval(pollInterval.current);
        syncCallback();
        setCompletedSyncItems(0);
        setTotalSyncItems(0);
      } else {
        setCompletedSyncItems(done);
      }
    });
  }

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

      {unsyncedPosts && unsyncedPosts.length > 0 && totalSyncItems === 0 && (
        <LoadingButton
          className="sync-all"
          text={"Sync all"}
          callback={syncAll}
          loadingText={"Syncing posts..."}
        />
      )}
      {totalSyncItems > 0 && 
        <span>Synced {completedSyncItems} / {totalSyncItems}</span>
      }

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
