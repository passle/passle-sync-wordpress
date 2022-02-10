import "./App.scss";
import { PostDataContextProvider } from "_Contexts/PostDataContext";
import SyncedPosts from "_Components/Organisms/SyncedPosts/SyncedPosts";
import SyncSettings from "_Components/Organisms/SyncSettings/SyncSettings";
import UnsyncedPosts from "_Components/Organisms/UnsyncedPosts/UnsyncedPosts";
import { setAPIKey } from "_Services/APIService";

export type AppProps = {
  pluginApiKey: string;
  clientApiKey: string;
  passleShortcodes: string;
};

const App = (props: AppProps) => {
  // React doesn't load data from Passle, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  setAPIKey(props.pluginApiKey);

  return (
    <div className="App">
      <PostDataContextProvider>
        <h1>Passle Sync - Settings</h1>
        <SyncSettings
          pluginApiKey={props.pluginApiKey}
          clientApiKey={props.clientApiKey}
          passleShortcodes={props.passleShortcodes}
        />

        <hr />

        <SyncedPosts />

        <hr />

        <UnsyncedPosts />
      </PostDataContextProvider>
    </div>
  );
};

export default App;
