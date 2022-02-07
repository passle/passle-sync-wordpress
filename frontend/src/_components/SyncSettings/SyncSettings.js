import { useState } from "react";
import { updateSettings } from "../../_services/APIService";
import LoadingButton from "../LoadingButton";
import "./SyncSettings.css";

function SyncSettings({ apiKey, passleShortcodes }) {
  const [bannerText, setBannerText] = useState('');
  const [savedApiKey, setApiKey] = useState(apiKey);
  const [savedShortcodes, setPassleShortcodes] = useState(passleShortcodes);
 
    const saveSettings = (finishLoadingCallback) => {
        updateSettings({
            apiKey: savedApiKey,
            passleShortcodes: savedShortcodes.replace(/\s/g, "").split(",")
        }).then((success) => {
            if (success) {
                setBannerText('Successfully updated settings');
            } else {
                setBannerText('Failed to update settings');
            }
            if (finishLoadingCallback) finishLoadingCallback();
        });
    }

    const updateAPIKey = (ev) => {
        setApiKey(ev.target.value);
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
                    API Key:
                    <input type="text" value={savedApiKey} onChange={updateAPIKey} />
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
