<?php 

namespace Passle\PassleSync\Services\Content\Passle;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Services\OptionsService;

class PassleTagGroupsContentService extends PassleContentServiceBase 
{
  private const TAG_GROUPS_CACHE_KEY = "passle_sync_tag_groups_cache";
  
  public static function get_cache()
  {
    $items = get_option(self::TAG_GROUPS_CACHE_KEY);

    if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
      $items = self::fetch_tag_groups();
    }

    return $items;
  }

  public static function overwrite_cache(array $data)
  {
    $success = update_option(self::TAG_GROUPS_CACHE_KEY, $data, false);

    if (!$success) {
      error_log('Failed to overwrite cache: ' . self::TAG_GROUPS_CACHE_KEY);
    }
  }

  public static function fetch_tag_groups() 
  {
    $options = OptionsService::get();

	$passle_shortcodes = $options->passle_shortcodes;

	$results = array_map(fn ($passle_shortcode) => static::fetch_tag_groups_by_passle($passle_shortcode), $passle_shortcodes);

    self::overwrite_cache($results);

    return $results;
  }

  public static function fetch_tag_groups_by_passle($passle_shortcode) {
    $path = "taggroups/$passle_shortcode";
  
    $url = (new UrlFactory())
      ->path($path)
	  ->build();

     return static::get($url);
  }
}