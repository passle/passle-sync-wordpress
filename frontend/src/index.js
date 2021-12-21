import React from "react";
import ReactDOM from "react-dom";
import "./index.css";
import App from "./App";
import reportWebVitals from "./reportWebVitals";

const passleSyncSettingsPageRoot = document.getElementById(
  "passle-sync-settings-root"
);

if (passleSyncSettingsPageRoot) {
  const settings = window.passleSyncSettings;

  ReactDOM.render(
    <React.StrictMode>
      <App settings={settings} />
    </React.StrictMode>,
    passleSyncSettingsPageRoot
  );
} else {
  console.error("Can't find passle-sync-settings-root");
}

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals(console.log);
