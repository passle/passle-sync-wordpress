<?php

namespace Passle\PassleSync\Models\Admin;

use Passle\PassleSync\Utils\Utils;

class PaginatedResponse
{
  public static function make(array $data, int $current_page, int $items_per_page, array $additional_data = [])
  {
    $items_per_page = Utils::clamp($items_per_page, 10, 100);
    $total_pages = ceil(count($data) / $items_per_page);
    $current_page = Utils::clamp($current_page, 1, $total_pages);
    $total_items = count($data);

    $paginated_data = array_slice($data, ($current_page - 1) * $items_per_page, $items_per_page);

    $response = array_merge($additional_data, [
      "data" => $paginated_data,
      "current_page" => $current_page,
      "items_per_page" => $items_per_page,
      "total_items" => $total_items,
      "total_pages" => $total_pages,
    ]);

    return $response;
  }
}
