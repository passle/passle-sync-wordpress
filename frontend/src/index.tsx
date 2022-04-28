import * as ReactDOM from "react-dom";
import App from "./App";
import "./index.scss";

const passleSyncSettingsPageRoot = document.getElementById(
  "passle-sync-settings-root",
);

if (passleSyncSettingsPageRoot) {
  ReactDOM.render(
    <App
      {...{
        options: JSON.parse(
          passleSyncSettingsPageRoot.dataset.passlesyncOptions,
        ),
      }}
    />,
    passleSyncSettingsPageRoot,
  );
} else {
  console.warn("Can't find passle-sync-settings-root");
}
