***

# PasslePost

This class provides a simple interface for accessing properties of
Passle posts that have been saved to the Wordpress database.



* Full name: `\Passle\PassleSync\Models\PasslePost`



## Properties


### shortcode

The shortcode for the post.

```php
public string $shortcode
```






***

### passle_shortcode

The shortcode for the Passle the post is published in.

```php
public string $passle_shortcode
```






***

### url

The URL for the post.

```php
public string $url
```






***

### slug

The slug used in the post URL.

```php
public string $slug
```






***

### title

The title of the post.

```php
public string $title
```






***

### content

The post content as HTML.

```php
public string $content
```






***

### authors

A list containing the details of the primary authors of this post.

```php
public ?array $authors
```






***

### primary_author

The primary author in the list of Passle authors.

```php
public \Passle\PassleSync\Models\PassleAuthor $primary_author
```






***

### coauthors

A list containing the details of the co-authors of this post.

```php
public ?array $coauthors
```






***

### share_views

A list showing how often the post has been viewed via different social media channels.

```php
public ?array $share_views
```







***

### total_shares

An integer showing how many times this post has been shared.

```php
public int $total_shares
```






***

### total_likes

An integer showing how many times the post has been liked.

```php
public int $total_likes
```






***

### date

A datetime value showing when this post was published.

```php
public \DateTime $date
```






***

### tags

A list of tags for this post.

```php
public ?array $tags
```






***

### is_repost

A boolean value showing whether this post is a repost of an original post.

```php
public bool $is_repost
```






***

### estimated_read_time_seconds

An integer showing the estimated time to read the post, in seconds.

```php
public int $estimated_read_time_seconds
```






***

### estimated_read_time_minutes

An integer showing the estimated time to read the post, in minutes.

```php
public int $estimated_read_time_minutes
```






***

### image_url

The URL for the post's featured media.

```php
public string $image_url
```






***

### featured_item_html

The HTML content for the post's featured media.

```php
public string $featured_item_html
```






***

### featured_item_position

An integer showing where the featured media is shown in the post. Values are: 0 - None; 1 - At the bottom of the post; 2 - At the top of the post; 3 - In the post’s header.

```php
public int $featured_item_position
```






***

### featured_item_media_type

An integer showing what type of media the post's featured media is. 0 - None; 1 - Image; 2 - Video; 3 - Audio; 4 - Embedded link / item; 5 - Font; 6 - Document.

```php
public int $featured_item_media_type
```






***

### featured_item_embed_type

An integer showing what type of embed the post's embedded item is, if the featured media is of type '4 - Embedded link / item'. 0 - None; 1 - Photo; 2 - Video; 3 - Link; 4 - Rich.

```php
public int $featured_item_embed_type
```






***

### featured_item_embed_provider

A string showing what provider the embedded item came from, if the featured media is of type '4 - Embedded link / item'.

```php
public string $featured_item_embed_provider
```






***

### excerpt

The first few lines of the post.

```php
public string $excerpt
```






***

### opens_in_new_tab

A boolean value showing if the post should open in a new tab.

```php
public bool $opens_in_new_tab
```






***

### quote_text

The text used in the post's quote.

```php
public string $quote_text
```






***

### quote_url

The URL for the post's quote.

```php
public string $quote_url
```






***

## Methods


### __construct

Construct a new instance of the `PasslePost` class from the Wordpress post object.

```php
public __construct(\WP_Post $wp_post, array $options = []): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$wp_post` | **\WP_Post** | The Wordpress post object. |
| `$options` | **array** | {<br />   Optional. Array containing options to be used when constructing the class.<br /><br />   @type bool $load_authors Whether authors should be loaded. Default &#039;true&#039;.<br /><br />   @type bool $tags Whether tags  should be loaded. Default &#039;true&#039;.<br />} |




***

### get_date

Get the date in the specified format.

```php
public get_date(string $format): \DateTime
```

Format string should use [standard PHP formatting options](https://www.php.net/manual/en/datetime.format.php).






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$format` | **string** | The formatting options for the string. |




***

### get_tag_names

Get an array containing the name of each tag.

```php
public get_tag_names(): string[]
```











***

### get_joined_tags

Get the tags as a comma separated string.

```php
public get_joined_tags(): string
```











***


***
> Automatically generated from source code comments on 2022-05-30 using [phpDocumentor](http://www.phpdoc.org/) and [saggre/phpdocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown)
