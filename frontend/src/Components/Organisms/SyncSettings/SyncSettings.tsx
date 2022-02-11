import { useState } from "react";
import Button from "_Components/Atoms/Button/Button";
import { setAPIKey, updateSettings } from "_Services/APIService";
import classNames from "_Utils/classNames";

type Notice = {
  text: string;
  success: boolean;
};

export type SyncSettingsProps = {
  pluginApiKey: string;
  clientApiKey: string;
  passleShortcodes: string;
};

const SyncSettings = (props: SyncSettingsProps) => {
  const [notice, setNotice] = useState<Notice>(null);

  const [pluginApiKey, setPluginApiKey] = useState(props.pluginApiKey);
  const [clientApiKey, setClientApiKey] = useState(props.clientApiKey);
  const [passleShortcodes, setPassleShortcodes] = useState(
    props.passleShortcodes,
  );

  const saveSettings = (finishLoadingCallback: () => void) => {
    updateSettings({
      pluginApiKey: pluginApiKey,
      clientApiKey: clientApiKey,
      passleShortcodes: passleShortcodes.replace(/\s/g, "").split(","),
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
                value={clientApiKey}
                onChange={(e) => setClientApiKey(e.target.value)}
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
                onChange={(e) => setPassleShortcodes(e.target.value)}
              />
              <p className="description" id="passle-shortcodes-description">
                A comma-separated list of the shortcodes of the Passles you want
                to sync content from.
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
