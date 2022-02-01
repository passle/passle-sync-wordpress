import "./App.css";
import { useState, useEffect } from "react";
import {
  getWordPressPosts,
  deleteWordPressPosts,
  setAPIKey,
  setPassleShortcode,
} from "./_services/APIService";
import UnsyncedPosts from "./_components/UnsyncedPosts/UnsyncedPosts";
import SyncedPosts from "./_components/SyncedPosts/SyncedPosts";
import LoadingButton from "./_components/LoadingButton";

function App({ settings }) {
  const [syncedPosts, setSyncedPosts] = useState([]);

  // TODO: think about this - React doesn't load data, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  // TODO: Support multiple passles
  setAPIKey(settings["api_key"]);
  setPassleShortcode(settings["passle_shortcode"]);

  useEffect(() => {
    async function initialFetch() {
      await fetchSyncedPosts(null);
    }
    initialFetch();
  }, []);

  const fetchSyncedPosts = async (finishLoadingCallback) => {
    const result = await getWordPressPosts();
    if (finishLoadingCallback) finishLoadingCallback();
    setSyncedPosts(result);
  };

  const deleteSyncedPosts = async (finishLoadingCallback) => {
    const result = await deleteWordPressPosts();
    await fetchSyncedPosts(finishLoadingCallback);
  };

  return (
    <div className="App">
      <h1>Passle Sync - Settings</h1>
      <SyncedPosts posts={syncedPosts} deleteSyncedPosts={deleteSyncedPosts} />

      <hr />
      
      <UnsyncedPosts
        syncCallback={() => fetchSyncedPosts(null)}
        syncedPosts={syncedPosts}
      />
    </div>
  );
}

export default App;
