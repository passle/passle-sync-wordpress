import { useContext, useMemo, useState } from "react";
import { PostDataContext } from "_Contexts/PassleDataContext";
import Button from "_Components/Atoms/Button/Button";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import Table from "_Components/Molecules/Table/Table";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import {
  deleteAllPosts,
  deleteManyPosts,
  refreshAllPosts,
  syncAllPosts,
  syncManyPosts,
} from "_Services/SyncService";
import Badge from "_Components/Atoms/Badge/Badge";

const PostsTable = () => {
  const { postData, refreshPostLists, setCurrentPage } =
    useContext(PostDataContext);

  const [working, setWorking] = useState(false);

  const [selectedPosts, setSelectedPosts] = useState<string[]>([]);

  const allSelectedPostsAreSynced = useMemo(
    () =>
      postData.data
        .filter((post) => selectedPosts.includes(post.shortcode))
        .every((post) => post.synced),
    [selectedPosts, postData],
  );

  const refreshList = async () => {
    await refreshAllPosts();
  };

  const syncAll = async () => {
    await syncAllPosts();
  };

  const syncSelected = async () => {
    await syncManyPosts({
      shortcodes: selectedPosts,
    });
  };

  const deleteAll = async () => {
    await deleteAllPosts();
  };

  const deleteSelected = async () => {
    await deleteManyPosts({
      shortcodes: selectedPosts,
    });
  };

  const doWork = async (fn: () => Promise<void>, cb: () => void) => {
    setWorking(true);

    await fn();
    await refreshPostLists();

    setWorking(false);
    setSelectedPosts([]);
    cb();
  };

  return (
    <div>
      <Table
        currentPage={postData.current_page}
        itemsPerPage={postData.items_per_page}
        totalItems={postData.total_items}
        totalPages={postData.total_pages}
        setCurrentPage={setCurrentPage}
        ActionsLeft={
          <>
            <Button
              variant="secondary"
              text="Refresh Posts"
              loadingText="Refreshing Posts..."
              disabled={working}
              onClick={(cb) => doWork(refreshList, cb)}
            />
            {selectedPosts.length ? (
              <Button
                variant="secondary"
                text="Sync Selected Posts"
                loadingText="Syncing Posts..."
                disabled={working}
                onClick={(cb) => doWork(syncSelected, cb)}
              />
            ) : (
              <Button
                variant="secondary"
                text="Sync All Posts"
                loadingText="Syncing Posts..."
                disabled={working}
                onClick={(cb) => doWork(syncAll, cb)}
              />
            )}
          </>
        }
        ActionsRight={
          <>
            {selectedPosts.length ? (
              <Button
                variant="secondary"
                text="Delete Selected Posts"
                loadingText="Deleting Posts..."
                disabled={!allSelectedPostsAreSynced || working}
                onClick={(cb) => doWork(deleteSelected, cb)}
              />
            ) : (
              <Button
                variant="secondary"
                text="Delete All Synced Posts"
                loadingText="Deleting Posts..."
                disabled={!postData.data.length || working} // TODO: This needs to count synced posts.
                onClick={(cb) => doWork(deleteAll, cb)}
              />
            )}
          </>
        }
        Head={
          <>
            <td id="cb" className="manage-column column-cb check-column">
              <input
                id="cb-select-all-1"
                type="checkbox"
                checked={selectedPosts.length === postData.data.length}
                onChange={(e) =>
                  setSelectedPosts(
                    e.target.checked
                      ? postData.data.map((x) => x.shortcode)
                      : [],
                  )
                }
              />
            </td>
            <th>Title</th>
            <th>Excerpt</th>
            <th style={{ width: 150 }}>Authors</th>
            <th style={{ width: 150 }}>Published Date</th>
            <th style={{ width: 100 }}>Synced</th>
          </>
        }
        Body={
          postData.data.length ? (
            postData.data.map((post) => (
              <tr key={post.shortcode}>
                <th scope="row" className="check-column">
                  <input
                    id="cb-select-1"
                    type="checkbox"
                    value={post.shortcode}
                    checked={selectedPosts.includes(post.shortcode)}
                    onChange={(e) =>
                      setSelectedPosts((state) =>
                        e.target.checked
                          ? [...state, post.shortcode]
                          : state.filter((x) => x !== post.shortcode),
                      )
                    }
                  />
                </th>
                <td style={{ display: "flex", alignItems: "flex-start" }}>
                  <FeaturedItem
                    variant={FeaturedItemVariant.Url}
                    data={post.imageUrl}
                  />
                  <a href={post.postUrl} style={{ marginLeft: 12 }}>
                    {post.title}
                  </a>
                </td>
                {post.excerpt ? (
                  <td dangerouslySetInnerHTML={{ __html: post.excerpt }} />
                ) : (
                  <td>—</td>
                )}
                <td>{post.authors}</td>
                <td>{post.publishedDate}</td>
                <td>
                  <Badge
                    variant={post.synced ? "success" : "warning"}
                    text={post.synced ? "Synced" : "Unsynced"}
                  />
                </td>
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
