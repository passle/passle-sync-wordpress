import { PostDataContextProvider } from "_Contexts/PostDataContext";
import { setAPIKey } from "_Services/ApiService";
import Tabs from "_Components/Molecules/Tabs/Tabs";
import SyncSettings from "_Components/Organisms/SyncSettings/SyncSettings";
import PostsTable from "_Components/Organisms/PostsTable/PostsTable";

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
        <div className="wrap">
          <h1 className="wp-heading-inline">Passle Sync</h1>
          <hr className="wp-header-end" />

          <Tabs
            tabs={[
              {
                label: "Settings",
                Content: (
                  <SyncSettings
                    pluginApiKey={props.pluginApiKey}
                    clientApiKey={props.clientApiKey}
                    passleShortcodes={props.passleShortcodes}
                  />
                ),
              },
              {
                label: "Posts",
                Content: <PostsTable />,
              },
            ]}
          />
        </div>
      </PostDataContextProvider>
    </div>
  );
};

export default App;
