***

# PassleAuthor

This class provides a simple interface for accessing properties of
Passle authors that have been saved to the Wordpress database.



* Full name: `\Passle\PassleSync\Models\PassleAuthor`



## Properties


### name

The person's full name.

```php
public string $name
```






***

### shortcode

The shortcode for the person.

```php
public string $shortcode
```






***

### profile_url

The URL for the person's profile.

```php
public string $profile_url
```






***

### avatar_url

The URL for this person's avatar image.

```php
public string $avatar_url
```






***

### subscribe_link

The URL to the subscribe page for this person.

```php
public string $subscribe_link
```






***

### role

The tagline for this person.

```php
public string $role
```






***

### description

The profile description for this person.

```php
public string $description
```






***

### email_address

The person's email address.

```php
public string $email_address
```






***

### phone_number

The person's phone number.

```php
public string $phone_number
```






***

### linkedin_profile_link

The URL to the person's LinkedIn profile.

```php
public string $linkedin_profile_link
```






***

### facebook_profile_link

The URL to the person's Facebook profile.

```php
public string $facebook_profile_link
```






***

### twitter_screen_name

The person's Twitter screen name.

```php
public string $twitter_screen_name
```






***

### xing_profile_link

The URL to the person's Xing profile.

```php
public string $xing_profile_link
```






***

### skype_profile_link

The URL to the person's Skype profile.

```php
public string $skype_profile_link
```






***

### vimeo_profile_link

The URL to the person's Vimeo profile.

```php
public string $vimeo_profile_link
```






***

### youtube_profile_link

The URL to the person's YouTube profile.

```php
public string $youtube_profile_link
```






***

### stumbleupon_profile_link

The URL to the person's StumbleUpon profile.

```php
public string $stumbleupon_profile_link
```






***

### pinterest_profile_link

The URL to the person's Pinterest profile.

```php
public string $pinterest_profile_link
```






***

### instagram_profile_link

The URL to the person's Instagram profile.

```php
public string $instagram_profile_link
```






***

### personal_links

A list of titles and urls for any other links the person may have added to their profile.

```php
public ?array $personal_links
```






***

### location_detail

The city/region the person is in.

```php
public string $location_detail
```






***

### location_country

The country the person is in.

```php
public string $location_country
```






***

### location_full

The combined city/region and/or country the person is in, separated by a comma.

```php
public string $location_full
```






***

### company_tagline

The tagline for the client the person is in.

```php
public string $company_tagline
```






***

## Methods


### __construct

Construct a new instance of the `PassleAuthor` class from the Wordpress post object.

```php
public __construct(\Passle\PassleSync\Models\WP_Post $wp_author): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$wp_author` | **\Passle\PassleSync\Models\WP_Post** | The Wordpress post object. |




***

### get_avatar_url

Get the avatar URL, opionally specifying a fallback URL.

```php
public get_avatar_url(string|null $fallback_url = PASSLESYNC_DEFAULT_AVATAR_URL): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fallback_url` | **string&#124;null** | The fallback image URL if the author doesn&#039;t have an avatar. |




***


***
> Automatically generated from source code comments on 2022-05-09 using [phpDocumentor](http://www.phpdoc.org/) and [saggre/phpdocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown)
