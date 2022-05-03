import { Syncable } from "_API/Types/Syncable";

export type Person = Syncable & {
  profileUrl: string;
  avatarUrl: string;
  name: string;
  description: string;
  role: string;
};
