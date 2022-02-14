import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { Person } from "_API/Types/Person";
import { Post } from "_API/Types/Post";
import { get, post } from "./ApiService";


// Posts
export const getAllPosts = async (options: {
  currentPage: number;
  itemsPerPage: number;
}) => get<PaginatedResponse<Post>>("/posts", options);
export const refreshAllPosts = async () => get("/posts/refresh-all");
export const syncAllPosts = async () => post("/posts/sync-all");
export const deleteAllPosts = async () => post("/posts/delete-all");
export const syncManyPosts = async (options: { shortcodes: string[] }) =>
  post("/posts/sync-many", options);
export const deleteManyPosts = async (options: { shortcodes: string[] }) =>
  post("/posts/delete-many", options);

// People
export const getAllPeople = async (options: {
  currentPage: number;
  itemsPerPage: number;
}) => get<PaginatedResponse<Person>>("/people", options);
export const refreshAllPeople = async () => get("/people/refresh-all");
export const syncAllPeople = async () => post("/people/sync-all");
export const deleteAllPeople = async () => post("/people/delete-all");
export const syncManyPeople = async (options: { shortcodes: string[] }) =>
  post("/people/sync-many", options);
export const deleteManyPeople = async (options: { shortcodes: string[] }) =>
  post("/people/delete-many", options);

export const updateSettings = async (data: object) =>
  post("/settings/update", data);
