import {
  PassleDataContextProvider,
  PersonDataContext,
  PostDataContext,
} from "_Contexts/PassleDataContext";
import Tabs from "_Components/Molecules/Tabs/Tabs";
import SyncSettings from "_Components/Organisms/SyncSettings/SyncSettings";
import PostsTable from "_Components/Organisms/PostsTable/PostsTable";
import PeopleTable from "_Components/Organisms/PeopleTable/PeopleTable";
import { Options } from "_API/Types/Options";

export type AppProps = {
  options: Options;
};

const App = (props: AppProps) => {
  return (
    <div className="App">
      <PassleDataContextProvider>
        <div className="wrap">
          <h1 className="wp-heading-inline">Passle Sync</h1>
          <hr className="wp-header-end" />

          <PostDataContext.Consumer>
            {({ data: postData }) => (
              <PersonDataContext.Consumer>
                {({ data: personData }) => (
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
