import { useContext, useState } from "react";
import { NoticeType } from "_API/Types/NoticeType";
import Button from "_Components/Atoms/Button/Button";
import Notice from "_Components/Atoms/Notice/Notice";
import BoolSettingsInput from "_Components/Molecules/SettingsInput/BoolSettingsInput";
import TextSettingsInput from "_Components/Molecules/SettingsInput/TextSettingsInput";
import { PassleDataContext } from "_Contexts/PassleDataContext";
import useOptions from "_Hooks/useOptions";
import { updateSettings } from "_Services/SyncService";

const RemoteHostingSetup = () => {
  const { setLoading } = useContext(PassleDataContext);
  const [notice, setNotice] = useState<NoticeType>(null);

  const { options, setOptions } = useOptions();

  const [simulateRemoteHosting, setSimulateRemoteHosting] = useState(
    options.simulateRemoteHosting
  );
  const [useHttps, setUseHttps] = useState(options.useHttps);
  const [customDomain, setCustomDomain] = useState(options.customDomain);
  const [passlePermalinkPrefix, setPasslePermalinkPrefix] = useState(
    options.passlePermalinkPrefix
  );
  const [postPermalinkPrefix, setPostPermalinkPrefix] = useState(
    options.postPermalinkPrefix
  );
  const [personPermalinkPrefix, setPersonPermalinkPrefix] = useState(
    options.personPermalinkPrefix
  );

  const saveSettings = (finishLoadingCallback: () => void) => {
    setLoading(true);

    updateSettings({
      ...options,
      simulateRemoteHosting,
      useHttps,
      customDomain,
      passlePermalinkPrefix,
      postPermalinkPrefix,
      personPermalinkPrefix,
    }).then((options) => {
      setLoading(false);

      if (options) {
        setNotice({
          content: "Successfully updated settings.",
          success: true,
        });

        setOptions(options);
      } else {
        setNotice({
          content: "Failed to update settings.",
          success: false,
        });
      }
      if (finishLoadingCallback) finishLoadingCallback();
    });
  };

  return (
    <div>
      {notice && (
        <Notice
          type="success"
          content={notice.content}
          onDismiss={() => setNotice(null)}
        />
      )}

      <Notice
        type="error"
        content={
          <span>
            <b>
              Note: The settings on this page are for testing the setup of your
              integration.
            </b>
            <br />
            <span>
              If you're not sure what a setting does, please{" "}
              <a
                href="https://remote-hosting-documentation.passle.net/docs/getting-started/using-the-api/additional-headers"
                target="_blank"
              >
                read the docs
              </a>
              .
            </span>
          </span>
        }
      />

      <table className="form-table">
        <tbody>
          <BoolSettingsInput
            label="Simulate Remote Hosting"
            checked={simulateRemoteHosting}
            onChange={(e) => setSimulateRemoteHosting(e.target.checked)}
          />
          {simulateRemoteHosting && 
            <>
              <BoolSettingsInput
                label="Use Https"
                checked={useHttps}
                onChange={(e) => setUseHttps(e.target.checked)}
              />
              <TextSettingsInput
                label="Custom Domain"
                value={customDomain}
                onChange={(e) => setCustomDomain(e.target.value)}
              />
              <TextSettingsInput
                label="Passle Path"
                description="String used as part of the url structure for Passles."
                value={passlePermalinkPrefix}
                onChange={(e) => setPasslePermalinkPrefix(e.target.value)}
              />
              <TextSettingsInput
                label="Post Permalink Prefix"
                description="String used as part of the url structure for Posts."
                value={postPermalinkPrefix}
                onChange={(e) => setPostPermalinkPrefix(e.target.value)}
              />
              <TextSettingsInput
                label="Author Permalink Prefix"
                description="String used as part of the url structure for Authors."
                value={personPermalinkPrefix}
                onChange={(e) => setPersonPermalinkPrefix(e.target.value)}
              />
            </>
          }
        </tbody>
      </table>

      <p className="submit">
        <Button
          content="Save Changes"
          onClick={saveSettings}
          loadingContent={"Saving..."}
        />
      </p>
    </div>
  );
};

export default RemoteHostingSetup;
