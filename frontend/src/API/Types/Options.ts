export type Options = {
  passleApiKey: string;
  pluginApiKey: string;
  passleShortcodes: string[];
  postPermalinkTemplate: string;
  personPermalinkTemplate: string;
  previewPermalinkTemplate: string;
  passleSyncPollingUrl: string;
  simulateRemoteHosting: boolean;
  includePasslePostsOnHomePage: boolean;
  includePasslePostsOnTagPage: boolean;
  domainExt: string;
};
