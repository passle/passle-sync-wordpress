import "./App.css";
import { useState, useEffect } from "react";

function App({ settings }) {
  const [unsyncedPosts, setUnsyncedPosts] = useState([]);
  const [loadingUnsyncedPosts, setLoadingUnsyncedPosts] = useState(false);

  const [syncedPosts, setSyncedPosts] = useState([]);
  const [loadingSyncedPosts, setLoadingSyncedPosts] = useState(true);

  const fetchUnsyncedPosts = async () => {
    console.log("fetchUnsyncedPosts");
    setLoadingUnsyncedPosts(true);

    const response = await fetch(
      "http://wordpressdemo.test/wp-json/passlesync/v1/posts/api"
    );
    const result = await response.json();

    console.log("fetchUnsyncedPosts:", result);
    setLoadingUnsyncedPosts(false);
    setUnsyncedPosts(result);
  };

  const fetchSyncedPosts = async () => {
    console.log("fetchSyncedPosts");
    setLoadingSyncedPosts(true);

    const response = await fetch(
      "http://wordpressdemo.test/wp-json/passlesync/v1/posts"
    );
    const result = await response.json();

    console.log("fetchSyncedPosts:", result);
    setLoadingSyncedPosts(false);
    setSyncedPosts(result);
  };

  useEffect(async () => {
    await fetchSyncedPosts();
  }, []);

  return (
    <div className="App">
      <h1>Passle Sync - Settings</h1>
      <SyncedPosts posts={syncedPosts} />
      {loadingSyncedPosts && <p>Loading posts...</p>}

      <UnsyncedPosts posts={unsyncedPosts} />
      {!loadingUnsyncedPosts && (
        <button id="call-service" onClick={() => fetchUnsyncedPosts()}>
          Fetch Posts from Passle
        </button>
      )}
      {loadingUnsyncedPosts && <p>Fetching posts...</p>}
    </div>
  );
}

function SyncedPosts({ posts }) {
  console.log("SyncedPosts:", posts);

  return (
    <>
      <h2>Passle Posts in WordPress:</h2>
      {posts && posts.map((post) => <SyncedPost post={post} />)}
      {!posts && <h3>No posts exist</h3>}
    </>
  );
}

function SyncedPost({ post }) {
  console.log("SyncedPost:", post);

  return (
    <>
      <a href={post.PostUrl} target="_blank">
        <p>
          {post.PostShortcode} - {post.PostTitle}
        </p>
      </a>
    </>
  );
}

function UnsyncedPosts({ posts }) {
  console.log("Posts:", posts);

  return (
    <>
      <h2>Passle Posts from API:</h2>
      {posts && posts.map((post) => <UnsyncedPost post={post} />)}
      {!posts && <h3>No posts fetched yet</h3>}
    </>
  );
}

function UnsyncedPost({ post }) {
  const [loading, setLoading] = useState(false);
  console.log("Post:", post);

  const syncPost = async () => {
    console.log("syncPost");
    setLoading(true);

    const response = await fetch(
      "http://wordpressdemo.test/wp-json/passlesync/v1/post/update",
      {
        method: "POST",
        data: post,
      }
    );
    const result = await response.json();

    console.log("syncPost:", result);
    setLoading(false);
  };

  return (
    <>
      <a href={post.PostUrl} target="_blank">
        <p>
          {post.PostShortcode} - {post.PostTitle}
        </p>
      </a>
      {!loading && <button onClick={() => syncPost()}>Sync post</button>}
      {loading && <p>Syncing...</p>}
    </>
  );
}

export default App;
