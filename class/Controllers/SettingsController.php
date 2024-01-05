<?php

namespace Passle\PassleSync\Controllers;

use \WP_REST_Request;
use Passle\PassleSync\Models\Admin\Options;
use Passle\PassleSync\Services\OptionsService;
use WP_Error;

class SettingsController extends ControllerBase
{
  public static function init()
  {
    static::register_route("/settings/update", "POST", "update_api_settings");
  }

  public static function update_api_settings(WP_REST_Request $request)
  {
    $params = $request->get_params();

    if (!isset($params)) {
      return new WP_Error("no_params", "You must include data to update settings", ["status" => 400]);
    }

    $options = new Options(
      $params["passleApiKey"],
      $params["pluginApiKey"],
      $params["passleShortcodes"],
      $params["postPermalinkTemplate"],
      $params["personPermalinkTemplate"],
      $params["previewPermalinkTemplate"],
      $params["simulateRemoteHosting"],
      $params["includePasslePostsOnHomePage"],
      $params["includePasslePostsOnTagPage"],
      $params["includeCategoriesFromPassleTagGroups"],
      PASSLESYNC_DOMAIN_EXT,
      get_site_url(),
    );

    if (!preg_match("/{{PostShortcode}}/", $options->post_permalink_template)) {
      return new WP_Error("permalink_templates_invalid", "The post permalink template must contain the {{PostShortcode}} variable", ["status" => 400]);
    }

    if (!preg_match("/{{PersonShortcode}}/", $options->person_permalink_template)) {
      return new WP_Error("permalink_templates_invalid", "The person permalink template must contain the {{PersonShortcode}} variable", ["status" => 400]);
    }

    if (!empty($options->preview_permalink_template) && !preg_match("/{{PostShortcode}}/", $options->preview_permalink_template)) {
      return new WP_Error("permalink_templates_invalid", "The preview permalink template must contain the {{PostShortcode}} variable", ["status" => 400]);
    }

    if (!static::validate_permalink_template_uniqueness($options)) {
      return new WP_Error("permalink_templates_not_unique", "Permalink templates must be unique", ["status" => 400]);
    }

    if (!static::validate_permalink_template_allowed_variables($options)) {
      return new WP_Error("permalink_templates_invalid_variables", "Permalink templates must only contain allowed variables", ["status" => 400]);
    }

    OptionsService::set($options);

    return $options;
  }

  private static function validate_permalink_template_uniqueness(Options $options)
  {
    $permalink_templates = [
      $options->post_permalink_template,
      $options->person_permalink_template,
      $options->preview_permalink_template,
    ];

    $unique_permalink_templates = array_map(function ($template) {
      return preg_replace(
        "/{{(.*?)}}/",
        "",
        $template
      );
    }, $permalink_templates);

    $unique_permalink_templates = array_unique($permalink_templates);

    return count($unique_permalink_templates) === count($permalink_templates);
  }

  private static function validate_permalink_template_allowed_variables(Options $options)
  {
    $allowed_variables_base = ["PassleShortcode"];
    $allowed_variables_post =  [...$allowed_variables_base, "PostShortcode", "PostSlug"];
    $allowed_variables_person = [...$allowed_variables_base, "PersonShortcode", "PersonSlug"];
    $allowed_variablse_preview = $allowed_variables_post;

    return static::validate_single_permalink_template($options->post_permalink_template, $allowed_variables_post) &&
      static::validate_single_permalink_template($options->person_permalink_template, $allowed_variables_person) &&
      static::validate_single_permalink_template($options->preview_permalink_template, $allowed_variablse_preview);
  }

  private static function validate_single_permalink_template(string $template, array $allowed_variables)
  {
    $permalink_template_variables = [];
    preg_match_all("/{{(.*?)}}/", $template, $permalink_template_variables);

    $permalink_template_variables = $permalink_template_variables[1];
    $invalid_variables = array_diff($permalink_template_variables, $allowed_variables);

    return count($invalid_variables) === 0;
  }
}
