<?php 

namespace Passle\PassleSync\Services\Content\Passle;

use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Services\OptionsService;

class PassleTagGroupsContentService extends PassleContentServiceBase 
{
  public static function fetch_tag_groups() 
  {
    $options = OptionsService::get();

	$passle_shortcodes = $options->passle_shortcodes;

	$results = array_map(fn ($passle_shortcode) => static::fetch_tag_groups_by_passle($passle_shortcode), $passle_shortcodes);

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