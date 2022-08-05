<?php

namespace Passle\PassleSync\Models\Resources;

use UnexpectedValueException;

class ResourceBase
{
  const NAME_SINGULAR = "";
  const NAME_PLURAL = "";
  const DISPLAY_NAME_SINGULAR = "";
  const DISPLAY_NAME_PLURAL = "";
  const CONTROLLER_NAME = "";
  const RESPONSE_FACTORY_NAME = "";
  const PASSLE_CONTENT_SERVICE_NAME = "";
  const WORDPRESS_CONTENT_SERVICE_NAME = "";
  const SYNC_HANDLER_NAME = "";
  const ADMIN_MODEL_NAME = "";

  public string $name_singular;
  public string $name_plural;
  public string $display_name_singular;
  public string $display_name_plural;
  public string $controller_name;
  public string $response_factory_name;
  public string $passle_content_service_name;
  public string $wordpress_content_service_name;
  public string $sync_handler_name;
  public string $admin_model_name;

  final public function __construct()
  {
    if (self::NAME_SINGULAR === static::NAME_SINGULAR) throw new UnexpectedValueException("NAME_SINGULAR const not implemented");
    if (self::NAME_PLURAL === static::NAME_PLURAL) throw new UnexpectedValueException("NAME_PLURAL const not implemented");
    if (self::DISPLAY_NAME_SINGULAR === static::DISPLAY_NAME_SINGULAR) throw new UnexpectedValueException("DISPLAY_NAME_SINGULAR const not implemented");
    if (self::DISPLAY_NAME_PLURAL === static::DISPLAY_NAME_PLURAL) throw new UnexpectedValueException("DISPLAY_NAME_PLURAL const not implemented");
    if (self::CONTROLLER_NAME === static::CONTROLLER_NAME) throw new UnexpectedValueException("CONTROLLER_NAME const not implemented");
    if (self::RESPONSE_FACTORY_NAME === static::RESPONSE_FACTORY_NAME) throw new UnexpectedValueException("RESPONSE_FACTORY_NAME const not implemented");
    if (self::PASSLE_CONTENT_SERVICE_NAME === static::PASSLE_CONTENT_SERVICE_NAME) throw new UnexpectedValueException("PASSLE_CONTENT_SERVICE_NAME const not implemented");
    if (self::WORDPRESS_CONTENT_SERVICE_NAME === static::WORDPRESS_CONTENT_SERVICE_NAME) throw new UnexpectedValueException("WORDPRESS_CONTENT_SERVICE_NAME const not implemented");
    if (self::WORDPRESS_CONTENT_SERVICE_NAME === static::WORDPRESS_CONTENT_SERVICE_NAME) throw new UnexpectedValueException("WORDPRESS_CONTENT_SERVICE_NAME const not implemented");
    if (self::ADMIN_MODEL_NAME === static::ADMIN_MODEL_NAME) throw new UnexpectedValueException("ADMIN_MODEL_NAME const not implemented");

    $this->name_singular = static::NAME_SINGULAR;
    $this->name_plural = static::NAME_PLURAL;
    $this->display_name_singular = static::DISPLAY_NAME_SINGULAR;
    $this->display_name_plural = static::DISPLAY_NAME_PLURAL;
    $this->controller_name = static::CONTROLLER_NAME;
    $this->response_factory_name = static::RESPONSE_FACTORY_NAME;
    $this->passle_content_service_name = static::PASSLE_CONTENT_SERVICE_NAME;
    $this->wordpress_content_service_name = static::WORDPRESS_CONTENT_SERVICE_NAME;
    $this->sync_handler_name = static::SYNC_HANDLER_NAME;
    $this->admin_model_name = static::ADMIN_MODEL_NAME;
  }

  public function get_shortcode_name()
  {
    $shortcode_name = ucfirst($this->name_singular) . "Shortcode";

    return $shortcode_name;
  }

  public function get_api_parameter_shortcode_name()
  {
    return static::get_shortcode_name();
  }

  public function get_meta_shortcode_name()
  {
    $meta_shortcode_name = "{$this->display_name_singular}_shortcode";

    return $meta_shortcode_name;
  }

  public function get_meta_slug_name()
  {
    $meta_slug_name = "{$this->display_name_singular}_slug";

    return $meta_slug_name;
  }

  public function get_cache_storage_key()
  {
    $cache_storage_key = "passlesync_{$this->name_plural}_cache";

    return $cache_storage_key;
  }

  public function get_post_type()
  {
    $post_type = "passle-{$this->display_name_singular}";

    return $post_type;
  }

  public function get_schedule_group_name()
  {
    $schedule_group_name = "passle_{$this->name_plural}_sync";

    return $schedule_group_name;
  }
}
