import { Syncable } from "_API/Types/Syncable";

export type Post = Syncable & {
  postUrl: string;
  imageUrl: string;
  title: string;
  authors: string;
  excerpt: string;
  publishedDate: string;
};
