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

  const [passleApiKey, setPassleApiKey] = useState(options.passleApiKey);
  const [pluginApiKey, setPluginApiKey] = useState(options.pluginApiKey);
  const [passleShortcodes, setPassleShortcodes] = useState(
    options.passleShortcodes,
  );
  const [postPermalinkPrefix, setPostPermalinkPrefix] = useState(
    options.postPermalinkPrefix,
  );
  const [personPermalinkPrefix, setPersonPermalinkPrefix] = useState(
    options.personPermalinkPrefix,
  );
  const [previewPermalinkPrefix, setPreviewPermalinkPrefix] = useState(
    options.previewPermalinkPrefix,
  );
  const [includePasslePostsOnHomePage, setIncludePasslePostsOnHomePage] =
    useState(options.includePasslePostsOnHomePage);
  const [includePasslePostsOnTagPage, setIncludePasslePostsOnTagPage] =
    useState(options.includePasslePostsOnTagPage);

  const saveSettings = (finishLoadingCallback: () => void) => {
    setLoading(true);

    updateSettings({
      passleApiKey,
      pluginApiKey,
      passleShortcodes,
      postPermalinkPrefix,
      personPermalinkPrefix,
      previewPermalinkPrefix,
      includePasslePostsOnHomePage,
      includePasslePostsOnTagPage,
    }).then((options) => {
      setLoading(false);

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
      if (finishLoadingCallback) finishLoadingCallback();
    });
  };

  return (
    <div>
      {notice && (
        <Notice
          type="success"
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
          <TextSettingsInput
            label="Post Permalink Prefix"
            description="The prefix that will be used for post permalink URLs."
            value={postPermalinkPrefix}
            onChange={(e) => setPostPermalinkPrefix(e.target.value)}
          />
          <TextSettingsInput
            label="Person Permalink Prefix"
            description="The prefix that will be used for person permalink URLs."
            value={personPermalinkPrefix}
            onChange={(e) => setPersonPermalinkPrefix(e.target.value)}
          />
          <TextSettingsInput
            label="Preview Permalink Prefix"
            description="The prefix that will be used for preview permalink URLs."
            value={previewPermalinkPrefix}
            onChange={(e) => setPreviewPermalinkPrefix(e.target.value)}
          />
          <BoolSettingsInput
            label="Include Passle Posts on the Home Page?"
            description="Whether or not to include Passle posts in the WordPress query that generates the home page."
            checked={includePasslePostsOnHomePage}
            onChange={(e) => setIncludePasslePostsOnHomePage(e.target.checked)}
          />
          <BoolSettingsInput
            label="Include Passle Posts on the Tag Page?"
            description="Whether or not to include Passle posts in the WordPress query that generates the tag page."
            checked={includePasslePostsOnTagPage}
            onChange={(e) => setIncludePasslePostsOnTagPage(e.target.checked)}
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
