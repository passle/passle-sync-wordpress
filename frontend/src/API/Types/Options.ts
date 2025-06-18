export type Options = {
  passleApiKey: string;
  pluginApiKey: string;
  passleShortcodes: string[];
  postPermalinkTemplate: string;
  personPermalinkTemplate: string;
  previewPermalinkTemplate: string;
  simulateRemoteHosting: boolean;
  includePasslePostsOnHomePage: boolean;
  includePasslePostsOnTagPage: boolean;
  includePassleTagGroups: boolean;
  turnOffDebugLogging: boolean;
  domainExt: string;
};
