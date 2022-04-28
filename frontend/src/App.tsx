import {
  PassleDataContextProvider,
  PersonDataContext,
  PostDataContext,
} from "_Contexts/PassleDataContext";
import { setAPIKey } from "_Services/ApiService";
import Tabs from "_Components/Molecules/Tabs/Tabs";
import SyncSettings from "_Components/Organisms/SyncSettings/SyncSettings";
import PostsTable from "_Components/Organisms/PostsTable/PostsTable";
import PeopleTable from "_Components/Organisms/PeopleTable/PeopleTable";
import { Options } from "_API/Types/Options";

export type AppProps = {
  options: Options;
};

const App = (props: AppProps) => {
  // React doesn't load data from Passle, so doesn't need the Passle API Key
  // But it does need to communicate securely with WP, so it needs to validate there
  setAPIKey(props.options.pluginApiKey);

  return (
    <div className="App">
      <PassleDataContextProvider>
        <div className="wrap">
          <h1 className="wp-heading-inline">Passle Sync</h1>
          <hr className="wp-header-end" />

          <PostDataContext.Consumer>
            {({ postData }) => (
              <PersonDataContext.Consumer>
                {({ personData }) => (
                  <Tabs
                    tabs={[
                      {
                        label: "Settings",
                        Content: <SyncSettings options={props.options} />,
                      },
                      {
                        label: "Posts",
                        disabled: postData == null,
                        Content: <PostsTable />,
                      },
                      {
                        label: "People",
                        disabled: personData == null,
                        Content: <PeopleTable />,
                      },
                    ]}
                  />
                )}
              </PersonDataContext.Consumer>
            )}
          </PostDataContext.Consumer>
        </div>
      </PassleDataContextProvider>
    </div>
  );
};

export default App;
