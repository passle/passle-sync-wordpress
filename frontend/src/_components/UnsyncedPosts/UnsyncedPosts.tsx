import { useState, useEffect, useRef, useContext } from "react";
import {
  updatePost,
  syncAllPosts,
  checkSyncProgress,
} from "../../_services/APIService";
import UnsyncedPost from "./_components/UnsyncedPost/UnsyncedPost";
import PaginatedItems from "../Pagination/Pagination";
import "./UnsyncedPosts.scss";
import {
  fetchSyncedPosts,
  fetchUnsyncedPosts,
  PasslePost,
  updateUnsyncedPosts,
} from "__services/SyncService";
import { PostDataContext } from "__contexts/PostData";
import LoadingButton from "__components/Atoms/LoadingButton/LoadingButton";
import Post from "__components/Molecules/Post/Post";
import { SyncState } from "__types/Enums/SyncState";

export type UnsyncedPostsProps = {
  // syncCallback: () => void;
  // syncedPosts: WordpressPost[];
};

const UnsyncedPosts = (props: UnsyncedPostsProps) => {
  const {
    syncedPosts,
    unsyncedPosts,
    setSyncedPosts,
    setUnsyncedPosts,
    dedupePosts,
  } = useContext(PostDataContext);

  const [totalSyncItems, setTotalSyncItems] = useState(0);
  const [completedSyncItems, setCompletedSyncItems] = useState(0);
  let pollInterval = useRef(null);

  let posts: PasslePost[] = [];

  useEffect(() => {
    const initialFetch = async () => {
      let result = await fetchUnsyncedPosts(null);
      setUnsyncedPosts(result);
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
    posts = dedupePosts(unsyncedPosts);
  }, [unsyncedPosts, syncedPosts]);

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
        fetchSyncedPosts(finishLoadingCallback).then((result) =>
          setSyncedPosts(result)
        );
        setCompletedSyncItems(0);
        setTotalSyncItems(0);
      } else {
        setCompletedSyncItems(parseInt(done));
      }
    });
  };

  const syncPost = async (
    postId: string,
    finishLoadingCallback: () => void
  ) => {
    let matchingPosts = unsyncedPosts.filter(
      (post) => post.PostShortcode === postId
    );
    if (matchingPosts.length == 0) return;

    let post = matchingPosts[0];
    post = { ...post, syncState: SyncState.Syncing };

    const result = await updatePost(post);
    if (finishLoadingCallback) finishLoadingCallback();

    post = { ...post, syncState: SyncState.Synced };
    fetchSyncedPosts(finishLoadingCallback).then((result) =>
      setSyncedPosts(result)
    );
  };

  return (
    <>
      <h2>{unsyncedPosts.length} Unsynced Passle Posts:</h2>

      <LoadingButton
        className="update-list"
        text={"Update list"}
        callback={(callback) =>
          updateUnsyncedPosts(callback).then((result) =>
            setUnsyncedPosts(result)
          )
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
            <Post
              id={post.PostShortcode}
              key={post.PostShortcode}
              title={post.PostTitle}
              snippet={post.ContentTextSnippet}
              postUrl={post.PostUrl}
              imageUrl={post.ImageUrl}
              publishedDate={post.PublishedDate}
              authorNames={post.Authors.map((author) => author.Name)}
              syncPost={syncPost}
              syncState={post.syncState}
            />
          )}
        />
      )}

      {(!unsyncedPosts || !unsyncedPosts.length) && <h3>No posts fetched</h3>}
      {posts &&
        posts.length > 0 &&
        (!unsyncedPosts || !unsyncedPosts.length) && (
          <h3>All posts have been synced</h3>
        )}
    </>
  );
};

export default UnsyncedPosts;
