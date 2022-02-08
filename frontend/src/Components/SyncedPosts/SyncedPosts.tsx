import PaginatedItems from "../Pagination/Pagination";
import { WordpressPost } from "_Services/SyncService";
import { useContext } from "react";
import { PostDataContext } from "_Contexts/PostDataContext";
import { deleteWordPressPosts } from "_Services/APIService";
import Button from "_Components/Atoms/LoadingButton/LoadingButton";
import Post from "_Components/Molecules/Post/Post";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";

const SyncedPosts = () => {
  const { syncedPosts, setSyncedPosts } = useContext(PostDataContext);
  const areAnyPosts = syncedPosts && syncedPosts.length > 0;

  const deleteSyncedPosts = async (finishLoadingCallback: () => void) => {
    await deleteWordPressPosts();
    setSyncedPosts([]);

    if (finishLoadingCallback) finishLoadingCallback();
  };

  return (
    <>
      <h2>{syncedPosts.length} Passle Posts in WordPress:</h2>
      {areAnyPosts ? (
        <>
          <Button
            text={"Delete existing posts"}
            callback={deleteSyncedPosts}
            loadingText={"Loading posts..."}
          />
          <PaginatedItems
            items={syncedPosts}
            renderItem={(post: WordpressPost) => (
              <Post
                id={post.guid}
                key={post.ID}
                title={post.post_title}
                excerpt={post.post_preview}
                postUrl={post.guid}
                featuredItem={{
                  variant: FeaturedItemVariant.Url,
                  data: post.post_image,
                }}
                publishedDate={post.post_date_gmt}
                authorNames={[post.post_authors]}
              />
            )}
          />
        </>
      ) : (
        <NoPostsMessage />
      )}
    </>
  );
};

const NoPostsMessage = () => <p>No posts have been synced</p>;

export default SyncedPosts;
