import { useState } from "react";
import Button from "_Components/Atoms/LoadingButton/LoadingButton";
import { setAPIKey, updateSettings } from "_Services/APIService";
import "./SyncSettings.scss";

export type SyncSettingsProps = {
  pluginApiKey: string;
  clientApiKey: string;
  passleShortcodes: string;
};

const SyncSettings = (props: SyncSettingsProps) => {
  const [bannerText, setBannerText] = useState("");
  const [savedPluginApiKey, setPluginApiKey] = useState(props.pluginApiKey);
  const [savedClientApiKey, setClientApiKey] = useState(props.clientApiKey);
  const [savedShortcodes, setPassleShortcodes] = useState(
    props.passleShortcodes
  );

  const saveSettings = (finishLoadingCallback: () => void) => {
    updateSettings({
      pluginApiKey: savedPluginApiKey,
      clientApiKey: savedClientApiKey,
      passleShortcodes: savedShortcodes.replace(/\s/g, "").split(","),
    }).then((success) => {
      if (success) {
        setBannerText("Successfully updated settings");
        setAPIKey(savedPluginApiKey);
      } else {
        setBannerText("Failed to update settings");
      }
      if (finishLoadingCallback) finishLoadingCallback();
    });
  };

  return (
    <>
      <h2>Settings:</h2>

      {bannerText && <h2>{bannerText}</h2>}
      <div className="settings-container">
        <div className="setting">
          <span>
            Sync API Key:
            <input
              type="text"
              value={savedPluginApiKey}
              onChange={(ev) => setPluginApiKey(ev.target.value)}
            />
          </span>
        </div>
        <div className="setting">
          <span>
            Passle API Key:
            <input
              type="text"
              value={savedClientApiKey}
              onChange={(ev) => setClientApiKey(ev.target.value)}
            />
          </span>
        </div>
        <div className="setting">
          <span>
            Passle Shortcodes:
            <input
              type="text"
              value={savedShortcodes}
              onChange={(ev) => setPassleShortcodes(ev.target.value)}
            />
          </span>
        </div>
      </div>

      <Button text={"Save"} callback={saveSettings} loadingText={"Saving..."} />
    </>
  );
};

export default SyncSettings;
