import { useState, useEffect, useRef } from "react";
import {
  getPostsFromPassleApi,
  refreshPostsFromPassleApi,
  updatePost,
  syncAllPosts,
  checkSyncProgress,
} from "../../_services/APIService";
import UnsyncedPost from "./_components/UnsyncedPost/UnsyncedPost";
import LoadingButton from "../LoadingButton";
import PaginatedItems from "../Pagination/Pagination";
import "./UnsyncedPosts.scss";
import {
  fetchUnsyncedPosts,
  PasslePost,
  updateUnsyncedPosts,
  WordpressPost,
} from "__services/SyncService";

export type UnsyncedPostsProps = {
  syncCallback: () => void;
  syncedPosts: WordpressPost[];
};

const UnsyncedPosts = (props: UnsyncedPostsProps) => {
  const [posts, setPosts] = useState([]);
  const [unsyncedPosts, setUnsyncedPosts] = useState([]);
  const [totalSyncItems, setTotalSyncItems] = useState(0);
  const [completedSyncItems, setCompletedSyncItems] = useState(0);
  let pollInterval = useRef(null);

  useEffect(() => {
    const initialFetch = async () => {
      let result = await fetchUnsyncedPosts(null);
      setPosts(result);
    };

    const fetchSyncProgress = async () => {
      const syncDetails = await checkSyncProgress();
      const total = parseInt(syncDetails.total);
      const done = parseInt(syncDetails.done);

      setTotalSyncItems(total);
      setCompletedSyncItems(done);

      if (total > done) {
        pollInterval.current = setInterval(() => {
          checkProgress(null);
        }, 1000);
      }
    };

    initialFetch();
    fetchSyncProgress();
  }, []);

  useEffect(() => {
    // Set this as an effect whenever posts or syncedPosts changes
    // This catches any API post updates, but also handles the race condition on page load
    // where the synced and unsynced posts are loaded at the same time
    const unsynced = dedupePosts(posts);
    setUnsyncedPosts(unsynced);
  }, [posts, props.syncedPosts]);

  const dedupePosts = (unsyncedPosts: PasslePost[]) => {
    const syncedShortcodes = Array.isArray(props.syncedPosts)
      ? props.syncedPosts.map((p) => p.post_shortcode)
      : [];

    console.log(props.syncedPosts, unsyncedPosts);

    return Array.isArray(unsyncedPosts)
      ? unsyncedPosts.filter((p) => !syncedShortcodes.includes(p.PostShortcode))
      : [];
  };

  const syncAll = async (finishLoadingCallback: () => void) => {
    syncAllPosts(unsyncedPosts);
    setTotalSyncItems(unsyncedPosts.length);

    pollInterval.current = setInterval(() => {
      checkProgress(finishLoadingCallback);
    }, 1000);
  };

  const checkProgress = async (finishLoadingCallback: () => void) => {
    checkSyncProgress().then(({ total, done }) => {
      if (done === total) {
        if (finishLoadingCallback) finishLoadingCallback();
        clearInterval(pollInterval.current);
        props.syncCallback();
        setCompletedSyncItems(0);
        setTotalSyncItems(0);
      } else {
        setCompletedSyncItems(parseInt(done));
      }
    });
  };

  const syncPost = async (
    post: PasslePost,
    finishLoadingCallback: () => void
  ) => {
    post = { ...post, syncState: "syncing" };

    const result = await updatePost(post);
    if (finishLoadingCallback) finishLoadingCallback();

    post = { ...post, syncState: "synced" };
    if (props.syncCallback) props.syncCallback();
  };

  return (
    <>
      <h2>{unsyncedPosts?.length ?? 0} Unsynced Passle Posts:</h2>

      <LoadingButton
        className="update-list"
        text={"Update list"}
        callback={(callback) =>
          updateUnsyncedPosts(callback).then((result) => setPosts(result))
        }
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
      {totalSyncItems > 0 && (
        <span>
          Synced {completedSyncItems} / {totalSyncItems}
        </span>
      )}

      {unsyncedPosts && unsyncedPosts.length > 0 && (
        <PaginatedItems
          items={unsyncedPosts}
          renderItem={(post: PasslePost) => (
            <UnsyncedPost
              post={post}
              key={post.PostShortcode}
              syncPost={syncPost}
              syncState={post.syncState}
            />
          )}
        />
      )}

      {(!posts || !posts.length) && <h3>No posts fetched</h3>}
      {posts &&
        posts.length > 0 &&
        (!unsyncedPosts || !unsyncedPosts.length) && (
          <h3>All posts have been synced</h3>
        )}
    </>
  );
};

export default UnsyncedPosts;
