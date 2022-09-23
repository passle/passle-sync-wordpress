<?php

use Passle\PassleSync\Models\PasslePost;

class PassleAuthorWidget extends WP_Widget
{
  function __construct()
  {
    parent::__construct(
      'passle_author_widget',
      __('Passle Author Widget', 'passle_widget_domain'),
      array('description' => __('Shows author details for a Passle post', 'passle_widget_domain'),)
    );
  }

  function is_passle_post()
  {
    global $post;

    if (is_null($post)) return false;
    if (get_post_type($post) != PASSLESYNC_POST_TYPE) return false;
    return true;
  }

  function get_template()
  {
    return '<style>
				.widget_passle_author_widget .user-info {
					background: white;
					margin-left: 20px;
				}

				.widget_passle_author_widget .user-info__section {
					margin: 0 10px;
					padding: 30px;
				}

				.widget_passle_author_widget .user-info__section:not(:first-child) {
					border-top: 1px solid #cccccc;
				}

				.widget_passle_author_widget .user-info__avatar {
					max-width: 150px;
					height: 150px;
					margin: 0 auto;
				}

				.widget_passle_author_widget .avatar {
					width: 100%%;
					height: 100%%;
					border-radius: 50%%;
					border: 7px solid #eee;
					overflow: hidden;
					display: flex;
					justify-content: center;
					align-items: center;
					text-align: center;
				}

				.widget_passle_author_widget .avatar__image {
					object-fit: cover;
					width: 100%%;
					height: 100%%;
				}

				.widget_passle_author_widget .profile-name {
					font-size: 1em;
					line-height: 1.2;
					margin-bottom: 10px;
					padding: 10px 0;
					text-align: center;
				}

				.widget_passle_author_widget .user-info__section:first-child a {
					color: inherit;
				}

				.widget_passle_author_widget .user-info__section:first-child a:hover {
					color: #f9653b;
				}

				.widget_passle_author_widget .user-info__section:first-child a:hover .avatar {
					border: 7px solid #f9653b44;
				}

				.widget_passle_author_widget .role,
				.widget_passle_author_widget .location,
        .widget_passle_author_widget .profile-contact {
					text-align: center;
				}

				.widget_passle_author_widget .pt-4 {
					padding-top: 1.5rem !important;
				}
			</style>
			<div class="user-info" itemscope="" itemtype="http://schema.org/Person">
				<div class="user-info__personal-details user-info__section">
          <a href="%2$s">
            <div class="user-info__avatar" itemprop="image">
              <div class="avatar">
                <img class="avatar__image" src="%1$s" alt="Avatar" itemprop="image">
              </div>
            </div>
            <div class="profile-name" itemprop="name">
              %3$s %4$s %5$s
            </div>
          </a>
          <div>
            <div itemprop="jobTitle" class="role">%6$s</div>
            <div class="location">
              <i class="mr-1 fas fa-map-marker-alt profile-location-icon" itemprop="location"></i>
              %7$s
            </div>
          </div>
          <div class="user-info__sharing-buttons pt-4"></div>
        </div>
				<div class="user-info__contact-details user-info__section">
					<p class="profile-contact">
						<i class="mr-2 fas fa-envelope"></i>
						<a href="mailto:%8$s" class="profile-contact-email" itemprop="email">%8$s</a>
					</p>
				</div>
				<div class="user-info__description user-info__section">
					<h2>About me</h2>
					<p itemprop="about">%9$s</p>
				</div>
			</div>';
  }

  function build_author_html($args)
  {
    if ($this->is_passle_post()) {
      global $post;

      $passle_post = new PasslePost($post);
      $author = $passle_post->primary_author;

      $avatar_url = PASSLESYNC_DEFAULT_PROFILE_IMAGE;
      $profile_url = '/';
      $author_name = 'Deleted author';
      $author_role = '';
      $author_location = '';
      $author_email = '';
      $author_desc = '';

      if (!is_null($author)) {
        $avatar_url = esc_url($author->avatar_url);
        $profile_url = esc_url($author->profile_url);
        $author_name = wp_kses($author->name, array());
        $author_role = wp_kses($author->role, array());
        $author_location = wp_kses($author->location_full, array());
        $author_email = esc_html($author->email_address);
        $author_desc = wp_kses($author->description, array());
      }

      return sprintf(
        $this->get_template(),
        $avatar_url,
        $profile_url,
        $args['before_title'],
        $author_name,
        $args['after_title'],
        $author_role,
        $author_location,
        $author_email,
        $author_desc
      );
    }
    return '';
  }

  public function widget($args, $instance)
  {
    echo $args['before_widget'];

    if ($this->is_passle_post()) {
      echo $this->build_author_html($args);
    }

    echo $args['after_widget'];
  }
}
