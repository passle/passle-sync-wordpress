<?php 

namespace Passle\PassleSync\Services\Content\Passle;

use Passle\PassleSync\Utils\Utils;
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
	  $tag_groups_with_tag_aliases = static::create_tag_groups_with_tag_aliases($tag_groups, $tag_mappings);
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
	 
	$all_tag_groups = static::get($url);
    
     return $all_tag_groups["TagGroups"];
  }

  public static function fetch_tag_mappings_by_passle($passle_shortcode) {
    
	$page_number = 1;
	$page_size = 500;
	$path = "tagmappings";
   
	 $all_tag_mappings = array();
	 do {
		
		$parameters = array(
		  "PassleShortcode" => $passle_shortcode,
		  "PageNumber" => $page_number,
		  "ItemsPerPage" => $page_size
		);
	
		$url = (new UrlFactory())
		  ->path($path)
		  ->parameters($parameters)
		  ->build();
		 
		$paged_tag_mappings = static::get($url);
	    $all_tag_mappings = array_merge($all_tag_mappings, $paged_tag_mappings["TagMappings"]);
		
		$page_number++;
	 }
	 while(count($paged_tag_mappings["TagMappings"]) == $page_size);

     return $all_tag_mappings;
  }
  
  public static function create_tag_groups_with_tag_aliases($tag_groups, $tag_mappings)
  {
	if ($tag_groups == null) {
		return $tag_groups;
	}
	
	$tags = array_merge(...array_map(function($tag_group) { return $tag_group["Tags"]; }, $tag_groups));	

	if(empty($tags)) {
		return $tag_groups;
	}
	
    $wp_tag_names = Utils::get_HTML_decoded_wp_tag_names();
	
	foreach($tags as $tag) {
		
		$tag_aliases = array_reduce($tag_mappings, function($carry, $tag_mapping) use ($tag) { 
			if ($tag_mapping["Tag"] == $tag) {
			   $carry = $tag_mapping["Aliases"];	
			}
			return $carry;
		}, []);

		if (!empty($tag_aliases) && count(array_intersect($tag_aliases, $wp_tag_names)) != 0) {		
		
			$tag_groups_that_contain_tag = array_filter($tag_groups, function($tag_group) use ($tag) { 
				return in_array($tag, $tag_group["Tags"]); 
			});
			
			foreach($tag_groups as &$tag_group) {
				$tag_index = array_search($tag, $tag_group["Tags"]);
				if ($tag_index !== false) {
					$modified_tags = array_map(function($tag_group_tag) use ($tag, $tag_aliases) { 
						return $tag_group_tag === $tag ? $tag_aliases : [$tag_group_tag];
					}, $tag_group["Tags"]);
					$tag_group["Tags"] = array_unique(array_merge(...$modified_tags), SORT_STRING);
				}				
			}
	    }
	}

	return $tag_groups;
  }
}