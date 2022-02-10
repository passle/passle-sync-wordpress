import { SyncState } from "_API/Enums/SyncState";
import { PassleAuthor } from "_API/Types/PassleAuthor";

export type PasslePost = {
  PostShortcode: string;
  PostUrl: string;
  PostTitle: string;
  ContentTextSnippet: string;
  ImageUrl: string;
  PublishedDate: string;
  Authors: PassleAuthor[];
  SyncState: SyncState;
};
