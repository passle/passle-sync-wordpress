import SyncedPost from "./_components/SyncedPost/SyncedPost";
import LoadingButton from "../LoadingButton";
import PaginatedItems from "../Pagination/Pagination";
import { WordpressPost } from "__services/SyncService";

export type SyncedPostsProps = {
  posts: WordpressPost[];
  deleteSyncedPosts: (fn: () => void) => Promise<void>;
};

const SyncedPosts = (props: SyncedPostsProps) => {
  const posts = props.posts;
  const areAnyPosts = posts && posts.length > 0;

  return (
    <>
      <h2>{posts?.length ?? 0} Passle Posts in WordPress:</h2>
      {areAnyPosts && (
        <LoadingButton
          text={"Delete existing posts"}
          callback={props.deleteSyncedPosts}
          loadingText={"Loading posts..."}
        />
      )}
      {areAnyPosts && (
        <PaginatedItems
          items={posts}
          renderItem={(post: WordpressPost) => (
            <SyncedPost post={post} key={post.ID} />
          )}
        />
      )}
      {!areAnyPosts && <NoPostsMessage />}
    </>
  );
};

const NoPostsMessage = () => <p>No posts have been synced</p>;

export default SyncedPosts;
