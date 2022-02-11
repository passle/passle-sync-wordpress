export type PaginatedResponse<T> = {
  data: T[];
  current_page: number;
  items_per_page: number;
  total_items: number;
  total_pages: number;
};
