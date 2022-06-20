export type PaginatedResponse<T> = Record<string, any> & {
  data: T[];
  current_page: number;
  items_per_page: number;
  total_items: number;
  total_pages: number;
};
