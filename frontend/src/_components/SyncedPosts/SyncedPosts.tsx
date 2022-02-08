import PaginatedItems from "../Pagination/Pagination";
import { WordpressPost } from "__services/SyncService";
import { useContext } from "react";
import { PostDataContext } from "__contexts/PostData";
import { deleteWordPressPosts } from "__services/APIService";
import LoadingButton from "__components/Atoms/LoadingButton/LoadingButton";
import Post from "__components/Molecules/Post/Post";
import FeaturedItem from "__components/Atoms/FeaturedItem/FeaturedItem";
import { FeaturedItemType } from "__types/Enums/FeaturedItemType";

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
          <LoadingButton
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
                snippet={post.post_preview}
                postUrl={post.guid}
                featuredItem={
                  <FeaturedItem
                    data={post.post_image}
                    type={FeaturedItemType.Url}
                  />
                }
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
