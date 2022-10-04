import {
  PassleDataContext,
  PassleDataContextProvider,
  PersonDataContext,
  PostDataContext,
} from "_Contexts/PassleDataContext";
import Tabs from "_Components/Molecules/Tabs/Tabs";
import SyncSettings from "_Components/Organisms/SyncSettings/SyncSettings";
import PostsTable from "_Components/Organisms/PostsTable/PostsTable";
import PeopleTable from "_Components/Organisms/PeopleTable/PeopleTable";
import HealthCheck from "_Components/Organisms/HealthCheck/HealthCheck";

const App = () => {
  return (
    <div className="App">
      <PassleDataContextProvider>
        <div className="wrap">
          <h1 className="wp-heading-inline">Passle Sync</h1>
          <hr className="wp-header-end" />

          <PassleDataContext.Consumer>
            {({ loading }) => (
              <PostDataContext.Consumer>
                {({ data: postData }) => (
                  <PersonDataContext.Consumer>
                    {({ data: personData }) => (
                      <Tabs
                        loading={loading}
                        tabs={[
                          {
                            label: "Settings",
                            Content: <SyncSettings />,
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
                          {
                            label: "Health Check",
                            Content: <HealthCheck />,
                          },
                        ]}
                      />
                    )}
                  </PersonDataContext.Consumer>
                )}
              </PostDataContext.Consumer>
            )}
          </PassleDataContext.Consumer>
        </div>
      </PassleDataContextProvider>
    </div>
  );
};

export default App;
