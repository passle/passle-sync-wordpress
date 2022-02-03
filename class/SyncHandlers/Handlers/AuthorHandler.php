<?php

namespace Passle\PassleSync\SyncHandlers\Handlers;

use Passle\PassleSync\SyncHandlers\SyncHandlerBase;
use Passle\PassleSync\SyncHandlers\ISyncHandler;
use Passle\PassleSync\Utils\UrlFactory;
use Passle\PassleSync\Utils\Utils;
use Passle\PassleSync\Services\WordpressContentService;
use Passle\PassleSync\Services\PassleContentService;

class AuthorHandler extends SyncHandlerBase implements ISyncHandler
{
    private $shortcodeKey = "Shortcode";

    public function __construct(
        WordpressContentService $wordpress_content_service,
        PassleContentService $passle_content_service)
    {
        parent::__construct($wordpress_content_service, $passle_content_service);
    }

    protected function create_blank_item()
    {
        return $this->wordpress_content_service->create_new_blank_author();
    }

    protected function sync_all_impl()
    {
        $passle_authors = $this->passle_content_service->get_stored_passle_authors_from_api();
        $existing_authors = $this->wordpress_content_service->get_passle_authors();

        return $this->compare_items($passle_authors, $existing_authors, $shortcodeKey, 'author_shortcode');
    }

    protected function sync_one_impl(array $data)
    {
        $existing_author = $this->wordpress_content_service->get_passle_author_by_shortcode($data[$shortcodeKey]);

        if ($existing_author == null) {
            $new_author = $this->create_blank_item();
            $this->sync($new_author, $data);
        } else {
            $this->sync($existing_author, $data);
        }
    }

    protected function delete_all_impl()
    {
        $existing_authors = $this->wordpress_content_service->get_passle_authors();

        foreach ($existing_authors as $author) {
            $this->delete($author);
        }
    }

    protected function delete_one_impl(array $data)
    {
        $existing_author = $this->wordpress_content_service->get_passle_author_by_shortcode($data[$shortcodeKey]);

        if ($existing_author != null) {
            $this->delete($existing_author);
        }
    }

    protected function sync(object $author, array $data)
    {
        $this->wordpress_content_service->update_author_data($author, $data);
    }

    protected function delete(object $author)
    {
        $this->wordpress_content_service->delete_author($author->ID);
    }
}
