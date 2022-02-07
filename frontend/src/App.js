import "./App.css";
import { useState, useEffect } from "react";
import { setAPIKey } from "./_services/APIService";
import { fetchSyncedPosts, deleteSyncedPosts } from "./_services/SyncService";
import UnsyncedPosts from "./_components/UnsyncedPosts/UnsyncedPosts";
import SyncedPosts from "./_components/SyncedPosts/SyncedPosts";
import SyncSettings from "./_components/SyncSettings/SyncSettings";

function App({ pluginApiKey, clientApiKey, passleShortcodes }) {
  const [syncedPosts, setSyncedPosts] = useState([]);

  // React doesn't load data from Passle, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  setAPIKey(pluginApiKey);

  useEffect(() => {
    async function initialFetch() {
      let results = await fetchSyncedPosts(null);
      setSyncedPosts(results);
    }
    initialFetch();
  }, []);

  return (
    <div className="App">
      <h1>Passle Sync - Settings</h1>
      <SyncSettings pluginApiKey={pluginApiKey} clientApiKey={clientApiKey} passleShortcodes={passleShortcodes} />

      <hr />

      <SyncedPosts posts={syncedPosts} deleteSyncedPosts={(callback) => deleteSyncedPosts(callback).then(result => setSyncedPosts(result))} />

      <hr />
      
      <UnsyncedPosts
        syncCallback={() => fetchSyncedPosts(null).then(result => setSyncedPosts(result))}
        syncedPosts={syncedPosts}
      />
    </div>
  );
}

export default App;
