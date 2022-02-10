import { useContext } from "react";
import "./UnsyncedPosts.scss";
import { updateUnsyncedPosts } from "_Services/SyncService";
import { PostDataContext } from "_Contexts/PostDataContext";
import Button from "_Components/Atoms/LoadingButton/LoadingButton";
import Post from "_Components/Molecules/Post/Post";
import { SyncState } from "_API/Enums/SyncState";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import { updateAllPosts, updatePost } from "_Services/APIService";
import PaginatedItems from "_Components/Molecules/Pagination/Pagination";
import { PasslePost } from "_API/Types/PasslePost";

const UnsyncedPosts = () => {
  const { unsyncedPosts, setUnsyncedPosts, refreshPostLists } =
    useContext(PostDataContext);

  const areAnyPosts = unsyncedPosts && unsyncedPosts.length > 0;

  const syncAll = async (finishLoadingCallback: () => void) => {
    await updateAllPosts(unsyncedPosts);

    await refreshPostLists();
    if (finishLoadingCallback) finishLoadingCallback();
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
    post = { ...post, SyncState: SyncState.Syncing };

    await updatePost(post);

    post = { ...post, SyncState: SyncState.Synced };

    await refreshPostLists();
    if (finishLoadingCallback) finishLoadingCallback();
  };

  return (
    <>
      <h2>{unsyncedPosts.length} Unsynced Passle Posts:</h2>

      <Button
        className="update-list"
        text={"Update list"}
        callback={(callback) =>
          updateUnsyncedPosts(callback).then((result) =>
            setUnsyncedPosts(result)
          )
        }
        loadingText={"Fetching posts..."}
      />

      {areAnyPosts ? (
        <>
          <Button
            className="sync-all"
            text={"Sync all"}
            callback={syncAll}
            loadingText={"Syncing posts..."}
          />
          <PaginatedItems
            items={unsyncedPosts}
            renderItem={(post) => RenderAPIPost(post, syncPost)}
          />
        </>
      ) : (
        <p>All posts have been synced</p>
      )}
    </>
  );
};

const RenderAPIPost = (
  post: PasslePost,
  syncPost: (postId: string, finishLoadingCallback: () => void) => void
) => (
  <Post
    id={post.PostShortcode}
    key={post.PostShortcode}
    title={post.PostTitle}
    excerpt={post.ContentTextSnippet}
    postUrl={post.PostUrl}
    featuredItem={{
      variant: FeaturedItemVariant.Url,
      data: post.ImageUrl,
    }}
    publishedDate={post.PublishedDate}
    authorNames={post.Authors.map((author) => author.Name)}
    syncPost={syncPost}
    syncState={post.SyncState}
  />
);

export default UnsyncedPosts;
