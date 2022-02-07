import SyncedPost from "./_components/SyncedPost/SyncedPost";
import LoadingButton from "../LoadingButton";
import PaginatedItems from "../Pagination/Pagination";

function SyncedPosts({ posts, deleteSyncedPosts }) {
  console.log("SyncedPosts:", posts);

  return (
    <>
      <h2>{posts?.length ?? 0} Passle Posts in WordPress:</h2>
      {posts && posts.length > 0 && (
        <LoadingButton
          text={"Delete existing posts"}
          callback={deleteSyncedPosts}
          loadingText={"Loading posts..."}
        />
      )}
      {posts && posts.length > 0 && 
        <PaginatedItems 
          items={posts} 
          renderItem={(post) => <SyncedPost post={post} key={post.ID}/>}
        />}
      {!(posts && posts.length > 0) && <NoPostsMessage/>}
    </>
  );
}

const NoPostsMessage = () => <p>No posts have been synced</p>;

export default SyncedPosts;
