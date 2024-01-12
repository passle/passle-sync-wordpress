<?php 

namespace Passle\PassleSync\Services;

use Passle\PassleSync\Services\Content\Passle\PassleTagGroupsContentService;

class TaxonomyRegistryService 
{
    public static function init() {
        add_action('init', [static::class, "create_taxonomies"]);
    }

    private static function get_taxonomy_slug($name) {
        return str_replace(' ', '_', strtolower($name));
    }

    public static function create_taxonomies() 
    {
        $tag_groups_response = PassleTagGroupsContentService::fetch_tag_groups();
        $tag_groups = $tag_groups_response[0]["TagGroups"];
        foreach ($tag_groups as $tag_group) {
            $name = $tag_group["Name"];
            $slug = self::get_taxonomy_slug($name);
            if ($name && !taxonomy_exists($name)) {
                $args = array(
                  "hierarchical" => false,
                  "label" => $name,
                  "show_admin_column" => true,
                  "query_var" => true,
                  "rewrite" => array("slug" => $slug)
                );
                register_taxonomy($slug, array(PASSLESYNC_POST_TYPE), $args);
            }
            $tags = $tag_group["Tags"];
            foreach($tags as $tag) {
                $term_exists = term_exists($tag, $slug);
                if (!$term_exists) {
                    $term = wp_insert_term(
                        $tag,
                        $slug,
                        array(
                        "parent" => 0
                        )
                    );
                
                    if (is_wp_error($term)) {
                        error_log("Error creating term: " . $term->get_error_message() . PHP_EOL); 
                    }
                }
            }
        }
    }
}