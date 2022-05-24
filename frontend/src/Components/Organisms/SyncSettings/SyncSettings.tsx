import { useContext, useMemo, useState } from "react";
import { Options } from "_API/Types/Options";
import Button from "_Components/Atoms/Button/Button";
import SettingsInput from "_Components/Molecules/SettingsInput/SettingsInput";
import { PassleDataContext } from "_Contexts/PassleDataContext";
import { updateSettings } from "_Services/SyncService";
import classNames from "_Utils/classNames";

type Notice = {
  text: string;
  success: boolean;
};

const SyncSettings = () => {
  const { setLoading } = useContext(PassleDataContext);
  const [notice, setNotice] = useState<Notice>(null);

  const options = useMemo<Options>(
    () =>
      JSON.parse(
        document.getElementById("passle-sync-settings-root").dataset
          .passlesyncOptions,
      ),
    [],
  );

  const [passleApiKey, setPassleApiKey] = useState(options.passleApiKey);
  const [pluginApiKey, setPluginApiKey] = useState(options.pluginApiKey);
  const [passleShortcodes, setPassleShortcodes] = useState(
    options.passleShortcodes,
  );
  const [postPermalinkPrefix, setPostPermalinkPrefix] = useState(
    options.postPermalinkPrefix,
  );
  const [personPermalinkPrefix, setPersonPermalinkPrefix] = useState(
    options.personPermalinkPrefix,
  );

  const saveSettings = (finishLoadingCallback: () => void) => {
    setLoading(true);

    updateSettings({
      passleApiKey,
      pluginApiKey,
      passleShortcodes,
      postPermalinkPrefix,
      personPermalinkPrefix,
    }).then((options) => {
      setLoading(false);

      if (options) {
        setNotice({
          text: "Successfully updated settings.",
          success: true,
        });

        document.getElementById(
          "passle-sync-settings-root",
        ).dataset.passlesyncOptions = JSON.stringify(options);
      } else {
        setNotice({
          text: "Failed to update settings.",
          success: false,
        });
      }
      if (finishLoadingCallback) finishLoadingCallback();
    });
  };

  return (
    <div>
      {notice && (
        <div
          id="message"
          className={classNames(
            "notice is-dismissible",
            notice.success ? "notice-success" : "notice-error",
          )}>
          <p>{notice.text}</p>
          <button
            type="button"
            className="notice-dismiss"
            onClick={() => setNotice(null)}>
            <span className="screen-reader-text">Dismiss this notice.</span>
          </button>
        </div>
      )}

      <table className="form-table">
        <tbody>
          <SettingsInput
            label="Passle API Key"
            value={passleApiKey}
            onChange={(e) => setPassleApiKey(e.target.value)}
          />
          <SettingsInput
            label="Plugin API Key"
            value={pluginApiKey}
            onChange={(e) => setPluginApiKey(e.target.value)}
          />
          <SettingsInput
            label="Passle Shortcodes"
            description="A comma-separated list of the shortcodes of the Passles you want
                to sync content from."
            value={passleShortcodes}
            onChange={(e) =>
              setPassleShortcodes(e.target.value.replace(/\s/g, "").split(","))
            }
          />
          <SettingsInput
            label="Post Permalink Prefix"
            description="The prefix that will be used for post permalink URLs."
            value={postPermalinkPrefix}
            onChange={(e) => setPostPermalinkPrefix(e.target.value)}
          />
          <SettingsInput
            label="Person Permalink Prefix"
            description="The prefix that will be used for person permalink URLs."
            value={personPermalinkPrefix}
            onChange={(e) => setPersonPermalinkPrefix(e.target.value)}
          />
        </tbody>
      </table>

      <p className="submit">
        <Button
          text="Save Changes"
          onClick={saveSettings}
          loadingText={"Saving..."}
        />
      </p>
    </div>
  );
};

export default SyncSettings;
