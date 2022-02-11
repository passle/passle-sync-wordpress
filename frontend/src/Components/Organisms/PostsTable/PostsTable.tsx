import { useContext, useState } from "react";
import { PostDataContext } from "_Contexts/PostDataContext";
import Button from "_Components/Atoms/Button/Button";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import Table from "_Components/Molecules/Table/Table";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import styles from "./PostsTable.module.scss";
import {
  deleteWordPressPosts,
  refreshPostsFromPassleApi,
  updateAllPosts,
} from "_Services/SyncService";

const PostsTable = () => {
  const { postData, refreshPostLists } = useContext(PostDataContext);

  const [working, setWorking] = useState(false);

  const refreshList = async (cb: () => void) => {
    setWorking(true);

    await refreshPostsFromPassleApi();
    await refreshPostLists();

    setWorking(false);
    cb();
  };

  const syncAll = async (cb: () => void) => {
    setWorking(true);

    await updateAllPosts(postData.data);
    await refreshPostLists();

    setWorking(false);
    cb();
  };

  const deleteAll = async (cb: () => void) => {
    setWorking(true);

    await deleteWordPressPosts();
    await refreshPostLists();

    setWorking(false);
    cb();
  };

  return (
    <div>
      <Table
        currentPage={postData.current_page}
        itemsPerPage={postData.items_per_page}
        totalItems={postData.total_items}
        totalPages={postData.total_pages}
        ActionsLeft={
          <>
            <Button
              variant="secondary"
              text="Refresh Posts"
              loadingText="Refreshing Posts..."
              disabled={working}
              onClick={refreshList}
            />
            <Button
              variant="secondary"
              text="Sync All Posts"
              loadingText="Syncing Posts..."
              disabled={working}
              onClick={syncAll}
            />
          </>
        }
        ActionsRight={
          <>
            <Button
              variant="secondary"
              text="Delete Synced Posts"
              loadingText="Deleting Posts..."
              disabled={!postData.data.length || working} // TODO: This needs to count synced posts.
              onClick={deleteAll}
            />
          </>
        }
        Head={
          <>
            <th>Title</th>
            <th>Excerpt</th>
            <th style={{ width: 150 }}>Authors</th>
            <th style={{ width: 150 }}>Published Date</th>
          </>
        }
        Body={
          postData.data.length ? (
            postData.data.map((post) => (
              <tr key={post.shortcode}>
                <td style={{ display: "flex" }}>
                  <FeaturedItem
                    variant={FeaturedItemVariant.Url}
                    data={post.imageUrl}
                  />
                  <a href={post.postUrl} style={{ marginLeft: 12 }}>
                    {post.title}
                  </a>
                </td>
                <td dangerouslySetInnerHTML={{ __html: post.excerpt }} />
                <td>{post.authors}</td>
                <td>{post.publishedDate}</td>
              </tr>
            ))
          ) : (
            <tr className="no-items">
              <td colSpan={4}>No posts found.</td>
            </tr>
          )
        }
      />
    </div>
  );
};

export default PostsTable;
