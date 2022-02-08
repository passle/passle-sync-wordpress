import { setAPIKey } from "./Services/APIService";
import UnsyncedPosts from "./Components/UnsyncedPosts/UnsyncedPosts";
import SyncedPosts from "./Components/SyncedPosts/SyncedPosts";
import SyncSettings from "./Components/SyncSettings/SyncSettings";
import "./App.scss";
import { PostDataContextProvider } from "_Contexts/PostDataContext";

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
