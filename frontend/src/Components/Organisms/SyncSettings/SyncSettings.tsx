import { useContext, useState } from "react";
import { NoticeType } from "_API/Types/NoticeType";
import Button from "_Components/Atoms/Button/Button";
import Notice from "_Components/Atoms/Notice/Notice";
import BoolSettingsInput from "_Components/Molecules/SettingsInput/BoolSettingsInput";
import TextSettingsInput from "_Components/Molecules/SettingsInput/TextSettingsInput";
import { PassleDataContext } from "_Contexts/PassleDataContext";
import useOptions from "_Hooks/useOptions";
import { updateSettings } from "_Services/SyncService";

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
  const [includeCategoriesFromPassleTagGroups, setIncludeCategoriesFromPassleTagGroups] = useState(
    options.includeCategoriesFromPassleTagGroups
  );

  const saveSettings = async (finishLoadingCallback: () => void) => {
    setLoading(true);

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
        includeCategoriesFromPassleTagGroups,
      });

      if (options) {
        setNotice({
          content: "Successfully updated settings.",
          success: true,
        });

        setOptions(options);
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
            label="Include categories from Passle tag groups"
            description="Whether to create categories from tag groups defined in Passle. If checked, syncing will create categories that correspond to tag groups and include Passle posts in them, based on the tags on each post. If no tag groups are defined in Passle or this option is unchecked, all Passle posts will be included in the default caterogy."
            checked={includeCategoriesFromPassleTagGroups}
            onChange={(e) => setIncludeCategoriesFromPassleTagGroups(e.target.checked)}
          />
        </tbody>
      </table>

      <p className="submit">
        <Button
          content="Save Changes"
          onClick={saveSettings}
          loadingContent={"Saving..."}
        />
      </p>
    </div>
  );
};

export default SyncSettings;
