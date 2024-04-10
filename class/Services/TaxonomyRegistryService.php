<?php 

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Services\Content\Passle\PassleTagGroupsContentService;

class TaxonomyRegistryService 
{
    public static function init() 
    {
        add_action('init', [static::class, "create_taxonomies"]);
    }

    private static function get_taxonomy_slug($name) 
    {
        return str_replace(' ', '_', strtolower($name));
    }

    public static function create_taxonomies() 
    {
      $tag_groups = PassleTagGroupsContentService::get_cache();
        
      if (empty($tag_groups)) {
        return;
      }

      foreach ($tag_groups as $tag_group) {
        
        if (!isset($tag_group["Name"])) {
          continue;
        }

        $name = $tag_group["Name"];
        $taxonomy_name = self::get_taxonomy_slug($name);
        
        if ($name && !taxonomy_exists($name)) {
          $args = array(
            "hierarchical" => false,
            "label" => $name,
            "show_admin_column" => true,
            "query_var" => true,
            "rewrite" => array("slug" => $taxonomy_name)
          );
          register_taxonomy($taxonomy_name, array(PASSLESYNC_POST_TYPE), $args);
        }

        if (!isset($tag_group["Tags"])) {
          continue;
        }
        
        $tags = $tag_group["Tags"];
        
        foreach ($tags as $tag) {
          $term_exists = term_exists($tag, $taxonomy_name);
          if (!$term_exists) {
            $term = wp_insert_term(
              $tag,
              $taxonomy_name,
              array(
                "parent" => 0
              )
            );
                
            if (is_wp_error($term)) {
              error_log("Error creating term " . $tag . ": " . $term->get_error_message() . PHP_EOL); 
            }
          }
        }
      }
    }
}