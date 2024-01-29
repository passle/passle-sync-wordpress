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

    $results = array_merge(...array_map(function ($passle_shortcode) { 
	  $tag_groups = static::fetch_tag_groups_by_passle($passle_shortcode);
	  $tag_mappings = static::fetch_tag_mappings_by_passle($passle_shortcode);
	  $tag_groups_with_tag_aliases = static::create_tag_groups_with_tag_aliases($tag_groups["TagGroups"], $tag_mappings["TagMappings"]);
	  return $tag_groups_with_tag_aliases; 
	}, $passle_shortcodes));

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

  public static function fetch_tag_mappings_by_passle($passle_shortcode) {
    $path = "tagmappings";
    
	$parameters = array(
      "PassleShortcode" => $passle_shortcode
    );
	
    $url = (new UrlFactory())
      ->path($path)
	  ->parameters($parameters)
	  ->build();

     return static::get($url);
  }
  
  public static function create_tag_groups_with_tag_aliases($tag_groups, $tag_mappings)
  {
	$tags = array_merge(...array_map(function($tag_group) { return $tag_group["Tags"]; }, $tag_groups));	
	
	if(empty($tags)) {
		return $tag_groups;
	}
	
	$wp_tags = get_tags(array("hide_empty" => false));
    $wp_tag_names = wp_list_pluck($wp_tags, "name");
	
	$modified_tag_groups = array();
	foreach($tags as $tag) {
	
		$tag_aliases = array_reduce($tag_mappings, function($carry, $tag_mapping) use ($tag) { 
			if ($tag_mapping["Tag"] == $tag) {
			   $carry = $tag_mapping["Aliases"];	
			}
			return $carry;
		}, []);
		
		if (empty($tag_aliases)) {
		   continue;
		}
			
		if (count(array_intersect($tag_aliases, $wp_tag_names)) == 0) {
			continue;
		}
		
		$tag_groups_that_contain_tag = array_filter($tag_groups, function($tag_group) use ($tag) { 
			return in_array($tag, $tag_group["Tags"]); 
		});
		
		foreach($tag_groups_that_contain_tag as $filtered_tag_group) {
			$tag_index = array_search($tag, $filtered_tag_group["Tags"]);
			if ($tag_index !== false) {
				array_splice($filtered_tag_group["Tags"], $tag_index, 1, $tag_aliases);
				$modified_tag_groups[] = $filtered_tag_group;
			}
		}
	}
	
	foreach($modified_tag_groups as $modified_tag_group) {
	   $tag_group_names = array_column($tag_groups, "Name");
	   $tag_group_index = array_search($modified_tag_group["Name"], $tag_group_names);
	   if ($tag_group_index !== false) {
	      $tag_groups[$tag_group_index] = $modified_tag_group;
	   }
	}
	
	return $tag_groups;
  }
}