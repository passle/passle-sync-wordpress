import { useContext, useState } from "react";
import { NoticeType } from "_API/Types/NoticeType";
import Button from "_Components/Atoms/Button/Button";
import Notice from "_Components/Atoms/Notice/Notice";
import BoolSettingsInput from "_Components/Molecules/SettingsInput/BoolSettingsInput";
import TextSettingsInput from "_Components/Molecules/SettingsInput/TextSettingsInput";
import { PassleDataContext } from "_Contexts/PassleDataContext";
import useOptions from "_Hooks/useOptions";
import { updateSettings, clearCache } from "_Services/SyncService";
import styles from "./SyncSettings.module.scss";

const SyncSettings = () => {
  const { setLoading } = useContext(PassleDataContext);
  const [notice, setNotice] = useState<NoticeType>(null);
  const { options, setOptions } = useOptions();

  const [passleApiKey, setPassleApiKey] = useState(
      options.passleApiKey
  );
  const [pluginApiKey, setPluginApiKey] = useState(
      options.pluginApiKey
  );
  const [passleShortcodes, setPassleShortcodes] = useState(
    options.passleShortcodes,
  );
  const [postPermalinkTemplate, setPostPermalinkTemplate] = useState(
    options.postPermalinkTemplate,
  );
  const [personPermalinkTemplate, setPersonPermalinkTemplate] = useState(
    options.personPermalinkTemplate,
  );
  const [previewPermalinkTemplate, setPreviewPermalinkTemplate] = useState(
    options.previewPermalinkTemplate,
  );
  const [simulateRemoteHosting, setSimulateRemoteHosting] = useState(
    options.simulateRemoteHosting,
  );
  const [includePasslePostsOnHomePage, setIncludePasslePostsOnHomePage] = useState(
    options.includePasslePostsOnHomePage
  );
  const [includePasslePostsOnTagPage, setIncludePasslePostsOnTagPage] = useState(
    options.includePasslePostsOnTagPage
  );
  const [includePassleTagGroups, setIncludePassleTagGroups] = useState(
    options.includePassleTagGroups
  );
  const [enableDebugLogging, setEnableDebugLogging] = useState(
    options.enableDebugLogging
  );

  const saveSettings = async (finishLoadingCallback: () => void) => {
    setLoading(true);

    let includePassleTagGroupsInitialValue = options.includePassleTagGroups;
    let enableDebugLoggingInitialValue = options.enableDebugLogging;
    
    try {
      const options = await updateSettings({
        passleApiKey,
        pluginApiKey,
        passleShortcodes,
        postPermalinkTemplate,
        personPermalinkTemplate,
        previewPermalinkTemplate,
        simulateRemoteHosting,
        includePasslePostsOnHomePage,
        includePasslePostsOnTagPage,
        includePassleTagGroups,
        enableDebugLogging
      });

      if (options) {
        setNotice({
          content: "Successfully updated settings.",
          success: true,
        });

        setOptions(options);
        
        // We need to reload the page so the plugin re-initializes when this option changes
          // and settings are subsequently saved
        if (includePassleTagGroupsInitialValue != includePassleTagGroups || enableDebugLoggingInitialValue != enableDebugLogging) {
          setTimeout(() => { window.location.reload(); }, 1000);
        }
      } else {
        setNotice({
          content: "Failed to update settings.",
          success: false,
        });
      }
    } catch (e) {
      setNotice({
        content: `Failed to update settings. ${e.response.data.message}.`,
        success: false,
      });
    }

    setLoading(false);
    if (finishLoadingCallback) finishLoadingCallback();
  };

  const clearCacheClick = async () => {
      setLoading(true);

      await clearCache();

      setTimeout(() => { window.location.reload(); }, 1000);

      setLoading(false);
  };

  return (
    <div>
      {notice && (
        <Notice
          type={notice.success ? "success" : "error"}
          content={notice.content}
          onDismiss={() => setNotice(null)}
        />
      )}

      <table className="form-table">
        <tbody>
          <TextSettingsInput
            label="Passle API Key"
            value={passleApiKey}
            onChange={(e) => setPassleApiKey(e.target.value)}
          />
          <TextSettingsInput
            label="Plugin API Key"
            value={pluginApiKey}
            onChange={(e) => setPluginApiKey(e.target.value)}
          />
          <TextSettingsInput
            label="Passle Shortcodes"
            description="A comma-separated list of the shortcodes of the Passles you want
                to sync content from."
            value={passleShortcodes}
            onChange={(e) =>
              setPassleShortcodes(e.target.value.replace(/\s/g, "").split(","))
            }
          />
          <tr>
            <th>Available Permalink Template Variables</th>
            <td>
              <ul style={{ margin: 0 }}>
                <li>
                  <strong>{"{{PassleShortcode}}"}</strong> - The Passle
                  shortcode
                </li>
                <li>
                  <strong>{"{{PostShortcode}}"}</strong> - The post shortcode
                  (post/preview template only)
                </li>
                <li>
                  <strong>{"{{PostSlug}}"}</strong> - The post slug (post
                  template only)
                </li>
                <li>
                  <strong>{"{{PersonShortcode}}"}</strong> - The person
                  shortcode (profile template only)
                </li>
                <li>
                  <strong>{"{{PersonSlug}}"}</strong> - The person slug (profile
                  template only)
                </li>
              </ul>
            </td>
          </tr>
          <TextSettingsInput
            label="Post Permalink Template"
            description="The template that will be used for post permalink URLs."
            value={postPermalinkTemplate}
            onChange={(e) => setPostPermalinkTemplate(e.target.value)}
          />
          <TextSettingsInput
            label="Person Permalink Template"
            description="The template that will be used for person permalink URLs."
            value={personPermalinkTemplate}
            onChange={(e) => setPersonPermalinkTemplate(e.target.value)}
          />
          <TextSettingsInput
            label="Preview Permalink Template"
            description="The template that will be used for preview permalink URLs."
            value={previewPermalinkTemplate}
            onChange={(e) => setPreviewPermalinkTemplate(e.target.value)}
          />
          <BoolSettingsInput
            label="Simulate Remote Hosting"
            description="Whether or not to force the Passle API to use the domain and paths of the WordPress site."
            checked={simulateRemoteHosting}
            onChange={(e) => setSimulateRemoteHosting(e.target.checked)}
          />
          <BoolSettingsInput
            label="Include Passle Posts on the Home Page"
            description="Whether or not to include Passle posts in the WordPress query that generates the home page."
            checked={includePasslePostsOnHomePage}
            onChange={(e) => setIncludePasslePostsOnHomePage(e.target.checked)}
          />
          <BoolSettingsInput
            label="Include Passle Posts on the Tag Page"
            description="Whether or not to include Passle posts in the WordPress query that generates the tag page."
            checked={includePasslePostsOnTagPage}
            onChange={(e) => setIncludePasslePostsOnTagPage(e.target.checked)}
          />
          <BoolSettingsInput
            label="Include Passle tag groups"
            description="Whether to create a custom taxonomy from tag groups defined in Passle. If checked, syncing will create taxonomy terms that correspond to tag groups and include set it on Passle posts based on the tags on each post. If you have existing taxonomies with the same name as tag groups defined in Passle, terms in them will be updated and these taxonomies will become available to Passle posts."
            checked={includePassleTagGroups}
            onChange={(e) => setIncludePassleTagGroups(e.target.checked)}
          />
          <BoolSettingsInput
            label="Enable DEBUG logs"
            description="If disabled, the plugin won't log errors in debug.log. Please enable to help with debugging."
            checked={enableDebugLogging}
            onChange={(e) => setEnableDebugLogging(e.target.checked)}
          />
        </tbody>
      </table>

      <p className={styles.SettingsActions}>
        <Button
          content="Save Changes"
          onClick={saveSettings}
          loadingContent={"Saving..."}
        />
        <Button
          variant="secondary"
          content="Clear Cache"
          onClick={clearCacheClick}
          loadingContent={"Clearing cache..."}
        />
      </p>
    </div>
  );
};

export default SyncSettings;
