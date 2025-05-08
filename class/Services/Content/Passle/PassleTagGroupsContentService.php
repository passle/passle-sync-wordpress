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
    $items = static::get_cached_items(self::TAG_GROUPS_CACHE_KEY);

    if (gettype($items) != "array" || count($items) == 0 || reset($items) == null) {
      $items = self::fetch_tag_groups();
    }

    return $items;
  }

  public static function overwrite_cache(?array $data)
  {
	$cache_storage_key = self::TAG_GROUPS_CACHE_KEY;

	if ($data == null) {
      static::clear_cached_items($cache_storage_key);
      return;
    }

	$chunks = array_chunk($data, 50);

    foreach ($chunks as $index => $chunk) {

	  // update_option will fail if we try to update it with the same value as it's current value
      $existing_items = get_option("{$cache_storage_key}_{$index}", false);

      if ($existing_items === $chunk) {
		error_log("No need to overwrite Tag groups cache key {$cache_storage_key}_{$index}. Existing value is the same as the new value.");
        continue;
      }

      $success = update_option("{$cache_storage_key}_{$index}", $chunk, false);
      
      if (!$success) {
		$existing_items_json = json_encode($existing_items);
		$chunk_json = json_encode($chunk);
        error_log("Failed to overwrite cache: {$cache_storage_key}_{$index}. Existing value: { $existing_items_json }. New value: { $chunk_json }");
      }
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

  public static function fetch_tag_groups_by_passle($passle_shortcode) 
  {
    $path = "taggroups/$passle_shortcode";
  
    $url = (new UrlFactory())
      ->path($path)
	  ->build();
	 
	$all_tag_groups = static::get($url);
    if (isset($all_tag_groups) && is_array($all_tag_groups) && isset($all_tag_groups["TagGroups"])) {
	  return $all_tag_groups["TagGroups"];
	}
	return array();
  }

  public static function fetch_tag_mappings_by_passle($passle_shortcode) 
  {
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
	   if (isset($paged_tag_mappings) && is_array($paged_tag_mappings) && isset($paged_tag_mappings["TagMappings"])) {
		$all_tag_mappings = array_merge($all_tag_mappings, $paged_tag_mappings["TagMappings"]);   
	   }
	   else {
		break;   
	   }
	   
	   $page_number++;
	 }
	 while(count($paged_tag_mappings["TagMappings"]) == $page_size);

     return $all_tag_mappings;
  }
  
  /**
  * Modifies tag groups, using tag mappings so the resulting tag groups contain the original tags
  * if a tag has no aliases or the aliases if an original tag has aliases.
  *
  * Each TagGroup should be an associative array with the following keys:
  *  - 'Name': string, the name of the tag group.
  *  - 'Tags': string[], an array of tags.
  *
  * Each TagMapping should be an associative array with the following keys:
  *  - 'Tag': string, the name of the tag.
  *  - 'Label': string, the label of the tag.
  *  - 'Aliases': string[], an array of aliases.
  *  - 'LastUpdated': string, an ISO date string.
  *
  * @param array<int, array{Name: string, Tags: string[]}> $tag_groups
  * @param array<int, array{Tag: string, Label: string, Aliases: string[], LastUpdated: string}> $tag_mappings
  * @return array<int, array{Name: string, Tags: string[]}> List of tag groups with original tags or aliases 
  *
  * e.g:
  * 
  * For $tag_groups like:
  * [{ "Name": "Tag Group A", "Tags": ["tagA", "tagB"] }]
  * And $tag_mappings like:
  * [{ "Tag": "tagA", "Label": "", "Aliases": ["tagA_alias"], "LastUpdated": "" }]
  * The output should be:
  * [{ "Name": "Tag Group A", "Tags": ["tagA_alias", "tagB"] }]
  *
  */
  public static function create_tag_groups_with_tag_aliases(array $tag_groups, array $tag_mappings) 
  {
	$tags = static::get_tags_from_tag_groups($tag_groups);

	if (empty($tags)) {
	  return $tag_groups;
	}

	foreach($tags as $tag) {
      $tag_aliases = static::get_tag_aliases_from_tag_mappings($tag, $tag_mappings);

	  if(empty($tag_aliases)) {
        continue;
	  }

	  foreach($tag_groups as &$tag_group) {
	    if (in_array($tag, $tag_group["Tags"], true)) {
		  static::modify_tag_group_with_aliases($tag_group, $tag, $tag_aliases);
		}				
	  }
	  unset($tag_group); // clear tag_group reference
	}

	return $tag_groups;
  }

  /**
  * Creates an associative array with keys equal to a tag
  * and values equal to the mappings of these tags as an array of strings.
  *
  * @param array<int, array{Tag: string, Label: string, Aliases: string[], LastUpdated: string?}> $tag_mappings
  * @return array<int, array<string, array{Aliases: string[]}>> Associative array of tags mapped to tag mappings
  *  
  * e.g:
  * For tag mappings like:
  * [{ "Tag": "tagA", "Label": "", "Aliases": ["tagA_alias"], "LastUpdated": "" }]
  * The output should be:
  * [{ "tagA": { "Aliases": ["tagA_alias"] } }]
  *
  */
  public static function create_tag_to_aliases_map(array $tag_mappings)
  {
	if ($tag_mappings == null) {
		return array();
	}

	$tag_to_aliases_map = array_map(function($tag_mapping) { return array($tag_mapping["Tag"] => array("Aliases" => $tag_mapping["Aliases"]));}, $tag_mappings);

	return $tag_to_aliases_map;
  }

  /**
  *  Extracts all tags from tag groups into a flat array.
  *
  * @param array<int, array{Name: string, Tags: string[]}> $tag_groups
  * @return string[] Flattened list of all tags
  */
  private static function get_tags_from_tag_groups(array $tag_groups) 
  {
    if ($tag_groups == null || empty($tag_groups)) {
      return array();
	}

	$tags = array_merge(...array_map(function($tag_group) { return $tag_group["Tags"]; }, $tag_groups));

	return $tags;
  }

  /**
  *  Extracts tag aliases for a given tag from a given array of tag mappings.
  *
  * @param string $tag
  * @param array<int, array{Tag: string, Label: string, Aliases: string[], LastUpdated: string}> $tag_mappings
  * @return string[] The aliases of the given tag
  */
  private static function get_tag_aliases_from_tag_mappings(string $tag, array $tag_mappings) 
  {
	if (empty($tag_mappings)) {
	  return array();
	}

    $tag_aliases = array_reduce($tag_mappings, function($carry, $tag_mapping) use ($tag) { 
	  if ($tag_mapping["Tag"] == $tag) {
	    $carry = $tag_mapping["Aliases"];	
	  }
	  return $carry;
	}, []);

	return $tag_aliases;
  }

  /**
  * Modifies the tag group in place by replacing a specific tag with its aliases.
  *
  * @param array $tag_group    Associative array with a key 'Tags' that is an array of strings; modified by reference.
  * @param string $tag         The tag to replace.
  * @param string[] $tag_aliases Aliases to replace the tag with.
  * @return void
  */
  private static function modify_tag_group_with_aliases(array &$tag_group, string $tag, array $tag_aliases) 
  {
	  $tag_index = array_search($tag, $tag_group["Tags"]);
	  if ($tag_index !== false) {
	    $modified_tags = array_map(function($tag_group_tag) use ($tag, $tag_aliases) { 
		  return $tag_group_tag === $tag ? $tag_aliases : [$tag_group_tag];
		}, $tag_group["Tags"]);
		$tag_group["Tags"] = array_unique(array_merge(...$modified_tags), SORT_STRING);
	  }
  }
}