import "./App.css";
import { useState, useEffect } from "react";
import { setAPIKey } from "./_services/APIService";
import { fetchSyncedPosts, deleteSyncedPosts } from "./_services/SyncService";
import UnsyncedPosts from "./_components/UnsyncedPosts/UnsyncedPosts";
import SyncedPosts from "./_components/SyncedPosts/SyncedPosts";
import SyncSettings from "./_components/SyncSettings/SyncSettings";

function App({ apiKey, passleShortcodes }) {
  const [syncedPosts, setSyncedPosts] = useState([]);

  // React doesn't load data from Passle, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  setAPIKey(apiKey);

  useEffect(() => {
    async function initialFetch() {
      await fetchSyncedPosts(null);
    }
    initialFetch();
  }, []);

  return (
    <div className="App">
      <h1>Passle Sync - Settings</h1>
      <SyncSettings apiKey={apiKey} passleShortcodes={passleShortcodes} />

      <hr />

      <SyncedPosts posts={syncedPosts} deleteSyncedPosts={deleteSyncedPosts} />

      <hr />
      
      <UnsyncedPosts
        syncCallback={() => fetchSyncedPosts(null).then(results => setSyncedPosts(results))}
        syncedPosts={syncedPosts}
      />
    </div>
  );
}

export default App;
