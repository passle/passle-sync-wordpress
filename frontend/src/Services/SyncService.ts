import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { Post } from "_API/Types/Post";
import { get, post } from "./ApiService";

export const refreshAllPosts = async () => get("/posts/refresh-all");

export const getAllPosts = async (options: {
  currentPage: number;
  itemsPerPage: number;
}) => get<PaginatedResponse<Post>>("/posts", options);

export const syncAllPosts = async () => post("/posts/sync-all");
export const deleteAllPosts = async () => post("/posts/delete-all");

export const syncManyPosts = async (options: { shortcodes: string[] }) =>
  post("/posts/sync-many", options);

export const deleteManyPosts = async (options: { shortcodes: string[] }) =>
  post("/posts/delete-many", options);

export const updateSettings = async (data: object) =>
  post("/settings/update", data);
