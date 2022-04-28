import { useState } from "react";
import { Options } from "_API/Types/Options";
import Button from "_Components/Atoms/Button/Button";
import { setAPIKey } from "_Services/ApiService";
import { updateSettings } from "_Services/SyncService";
import classNames from "_Utils/classNames";

type Notice = {
  text: string;
  success: boolean;
};

export type SyncSettingsProps = {
  options: Options;
};

const SyncSettings = (props: SyncSettingsProps) => {
  const [notice, setNotice] = useState<Notice>(null);

  const [passleApiKey, setPassleApiKey] = useState(props.options.passleApiKey);
  const [pluginApiKey, setPluginApiKey] = useState(props.options.pluginApiKey);
  const [passleShortcodes, setPassleShortcodes] = useState(
    props.options.passleShortcodes,
  );
  const [postPermalinkPrefix, setPostPermalinkPrefix] = useState(
    props.options.postPermalinkPrefix,
  );
  const [personPermalinkPrefix, setPersonPermalinkPrefix] = useState(
    props.options.personPermalinkPrefix,
  );

  const saveSettings = (finishLoadingCallback: () => void) => {
    updateSettings({
      pluginApiKey,
      passleApiKey,
      passleShortcodes,
      postPermalinkPrefix,
      personPermalinkPrefix,
    }).then((success) => {
      if (success) {
        setNotice({
          text: "Successfully updated settings.",
          success: true,
        });

        setAPIKey(pluginApiKey);
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
          <tr>
            <th className="row">
              <label htmlFor="plugin-api-key">Sync API Key</label>
            </th>
            <td>
              <input
                type="text"
                id="plugin-api-key"
                className="regular-text code"
                value={pluginApiKey}
                onChange={(e) => setPluginApiKey(e.target.value)}
              />
            </td>
          </tr>
          <tr>
            <th className="row">
              <label htmlFor="client-api-key">Passle API Key</label>
            </th>
            <td>
              <input
                type="text"
                id="client-api-key"
                className="regular-text code"
                value={passleApiKey}
                onChange={(e) => setPassleApiKey(e.target.value)}
              />
            </td>
          </tr>
          <tr>
            <th className="row">
              <label htmlFor="passle-shortcodes">Passle Shortcodes</label>
            </th>
            <td>
              <input
                type="text"
                id="passle-shortcodes"
                className="regular-text code"
                value={passleShortcodes}
                onChange={(e) =>
                  setPassleShortcodes(
                    e.target.value.replace(/\s/g, "").split(","),
                  )
                }
              />
              <p className="description" id="passle-shortcodes-description">
                A comma-separated list of the shortcodes of the Passles you want
                to sync content from.
              </p>
            </td>
          </tr>
          <tr>
            <th className="row">
              <label htmlFor="post-permalink-prefix">
                Post Permalink Prefix
              </label>
            </th>
            <td>
              <input
                type="text"
                id="post-permalink-prefix"
                className="regular-text code"
                value={postPermalinkPrefix}
                onChange={(e) => setPostPermalinkPrefix(e.target.value)}
              />
              <p className="description" id="post-permalink-prefix-description">
                The prefix that will be used for post permalink URLs.
              </p>
            </td>
          </tr>
          <tr>
            <th className="row">
              <label htmlFor="person-permalink-prefix">
                Person Permalink Prefix
              </label>
            </th>
            <td>
              <input
                type="text"
                id="person-permalink-prefix"
                className="regular-text code"
                value={personPermalinkPrefix}
                onChange={(e) => setPersonPermalinkPrefix(e.target.value)}
              />
              <p
                className="description"
                id="person-permalink-prefix-description">
                The prefix that will be used for person permalink URLs.
              </p>
            </td>
          </tr>
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
