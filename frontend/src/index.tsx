import * as ReactDOM from "react-dom";
import App from "./App";
import "./index.scss";

const passleSyncSettingsPageRoot = document.getElementById(
  "passle-sync-settings-root"
);

if (passleSyncSettingsPageRoot) {
  ReactDOM.render(
    <App
      {...{
        pluginApiKey: passleSyncSettingsPageRoot.dataset.pluginApiKey,
        clientApiKey: passleSyncSettingsPageRoot.dataset.clientApiKey,
        passleShortcodes: passleSyncSettingsPageRoot.dataset.passleShortcodes,
      }}
    />,
    passleSyncSettingsPageRoot
  );
} else {
  console.error("Can't find passle-sync-settings-root");
}
