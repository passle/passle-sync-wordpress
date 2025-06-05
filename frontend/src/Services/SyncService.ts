import { Options } from "_API/Types/Options";
import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { get, post } from "./ApiService";

export const getAll = async <T>(
  path: string,
  options: {
    currentPage: number;
    itemsPerPage: number;
  },
) => get<PaginatedResponse<T>>(`/${path}`, options);

export const refreshAll = async (path: string) =>
  get<void>(`/${path}/refresh-all`);

export const syncAll = async (path: string) => post<void>(`/${path}/sync-all`);

export const deleteAll = async (path: string) =>
  post<void>(`/${path}/delete-all`);

export const syncMany = async (
  path: string,
  options: { shortcodes: string[] },
) => post<void>(`/${path}/sync-many`, options);

export const deleteMany = async (
  path: string,
  options: { shortcodes: string[] },
) => post<void>(`/${path}/delete-many`, options);

export const syncOne = async (
  path: string,
  options: { shortcode: string },
) => post<void>(`/${path}/sync-one`, options);

export const deleteOne = async (
  path: string,
  options: { shortcode: string },
) => post<void>(`/${path}/delete-one`, options);

export const updateSettings = async (data: object) =>
  post<Options>("/settings/update", data);

export const clearCache = async () => 
  post<void>("/settings/clear-cache");
