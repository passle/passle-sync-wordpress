import { useState } from "react";
import { setAPIKey, updateSettings } from "../../_services/APIService";
import LoadingButton from "../LoadingButton";
import "./SyncSettings.css";

function SyncSettings({ pluginApiKey, clientApiKey, passleShortcodes }) {
  const [bannerText, setBannerText] = useState('');
  const [savedPluginApiKey, setPluginApiKey] = useState(pluginApiKey);
  const [savedClientApiKey, setClientApiKey] = useState(clientApiKey);
  const [savedShortcodes, setPassleShortcodes] = useState(passleShortcodes);
 
    const saveSettings = (finishLoadingCallback) => {
        updateSettings({
            pluginApiKey: savedPluginApiKey,
            clientApiKey: savedClientApiKey,
            passleShortcodes: savedShortcodes.replace(/\s/g, "").split(",")
        }).then((success) => {
            if (success) {
                setBannerText('Successfully updated settings');
                setAPIKey(savedPluginApiKey);
            } else {
                setBannerText('Failed to update settings');
            }
            if (finishLoadingCallback) finishLoadingCallback();
        });
    }

    const updatePluginAPIKey = (ev) => {
        setPluginApiKey(ev.target.value);
    }

    const updateClientAPIKey = (ev) => {
        setClientApiKey(ev.target.value);
    }

    const updateShortcodes = (ev) => {
        setPassleShortcodes(ev.target.value);
    }

  return (
    <>
        <h2>Settings:</h2>

        {bannerText && 
            <h2>{bannerText}</h2>
        }
        <div className="settings-container">
            <div className="setting">
                <span>
                    Sync API Key:
                    <input type="text" value={savedPluginApiKey} onChange={updatePluginAPIKey} />
                </span>
            </div>
            <div className="setting">
                <span>
                    Passle API Key:
                    <input type="text" value={savedClientApiKey} onChange={updateClientAPIKey} />
                </span>
            </div>
            <div className="setting">
                <span>
                    Passle Shortcodes:
                    <input type="text" value={savedShortcodes} onChange={updateShortcodes} />
                </span>
            </div>
        </div>


        <LoadingButton
          text={"Save"}
          callback={saveSettings}
          loadingText={"Saving..."}
        />
    </>
  );
}

export default SyncSettings;
