import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { PasslePost } from "_API/Types/PasslePost";
import { Post } from "_API/Types/Post";
import { get, post } from "./ApiService";

export const getAllPosts = async (options: {
  currentPage: number;
  itemsPerPage: number;
}) => get<PaginatedResponse<Post>>("/posts", options);

export const deleteWordPressPosts = async () => post("/posts/delete");

export const refreshPostsFromPassleApi = async () => get("/posts/refresh");

export const updatePost = async (data: PasslePost) =>
  post("/post/update", data);

export const updateAllPosts = async (data: object) =>
  post("/posts/update", data);

export const updateSettings = async (data: object) =>
  post("/settings/update", data);
