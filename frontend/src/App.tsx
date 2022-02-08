import { useState, useEffect } from "react";
import { setAPIKey } from "./_services/APIService";
import { fetchSyncedPosts, deleteSyncedPosts } from "./_services/SyncService";
import UnsyncedPosts from "./_components/UnsyncedPosts/UnsyncedPosts";
import SyncedPosts from "./_components/SyncedPosts/SyncedPosts";
import SyncSettings from "./_components/SyncSettings/SyncSettings";
import "./App.scss";

export type AppProps = {
  pluginApiKey: string;
  clientApiKey: string;
  passleShortcodes: string;
};

const App = (props: AppProps) => {
  const [syncedPosts, setSyncedPosts] = useState([]);

  // React doesn't load data from Passle, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  setAPIKey(props.pluginApiKey);

  useEffect(() => {
    const initialFetch = async () => {
      let results = await fetchSyncedPosts(null);
      setSyncedPosts(results);
    };
    initialFetch();
  }, []);

  return (
    <div className="App">
      <h1>Passle Sync - Settings</h1>
      <SyncSettings
        pluginApiKey={props.pluginApiKey}
        clientApiKey={props.clientApiKey}
        passleShortcodes={props.passleShortcodes}
      />

      <hr />

      <SyncedPosts
        posts={syncedPosts}
        deleteSyncedPosts={(callback) =>
          deleteSyncedPosts(callback).then((result) => setSyncedPosts(result))
        }
      />

      <hr />

      <UnsyncedPosts
        syncCallback={() =>
          fetchSyncedPosts(null).then((result) => setSyncedPosts(result))
        }
        syncedPosts={syncedPosts}
      />
    </div>
  );
};

export default App;
