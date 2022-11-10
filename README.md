# Passle Sync for Wordpress

> **Warning**
>
> There's a breaking change on the way. Once this change has been deployed to the Passle backend, the plugin won't auto-sync until you've updated to the latest version.

Passle Sync is a plugin for Wordpress which syncs your [Passle](https://home.passle.net/) posts and authors into your Wordpress instance.

Get started with the section below, or jump straight to the [API documentation](./docs/index.md).

A great example of how to use the plugin is our demo theme:

- [📂 Theme source](https://github.com/passle/passle-sync-wordpress-demo-theme)
- [🌍 Live demo](http://mercierandvelezviewpoints.com/)

## 🚀 Getting started

Get started by installing the plugin and activating it.

Once the plugin is installed, admin users can access the settings under **Settings > Passle Sync**.

### ⚙️ Configuration

On the first tab of the plugin settings page, you will find the following configuration options:

| Option                  | Description                                                                                                    |
| ----------------------- | -------------------------------------------------------------------------------------------------------------- |
| Passle API Key          | The API key generated in the Passle dashboard, used to fetch content from Passle.                              |
| Plugin API Key          | The API key Passle should use when calling the plugin webhooks after content is updated.                       |
| Passle Shortcodes       | A comma-separated list of the shortcodes of the Passles you want to sync content from.                         |
| Post Permalink Prefix   | The prefix that will be used for post permalink URLs. This needs to match what is set in the Passle backend.   |
| Person Permalink Prefix | The prefix that will be used for person permalink URLs. This needs to match what is set in the Passle backend. |

### 📙 Basic Usage

Once the plugin has been configured correctly, posts and people can be synced using the **Posts** and **People** tabs under **Settings > Passle Sync**.

**1. Fetch from API**

First, the plugin has to fetch posts and authors from the Passle API. Use the **Fetch Passle Posts** and **Fetch Passle People** buttons to do so. Once the plugin has done the initial fetches from the API, the API responses will be cached, so if you reload the page, the posts and authors you have fetched will be remembered.

**2. Sync to Wordpress**

To sync the posts and authors to Wordpress, use the **Sync All Posts** and **Sync All People** buttons. This will create a new Wordpress post for each post/author under a custom post type. Once all posts/authors have been synced, their statuses will update. Synced posts and authors can be viewed, but not edited, under the Passle Posts and Passle Authors menu items in the sidebar.

**3. Webhooks**

Whenever a post or author is updated through the Passle interface, the Passle backend will make a call to a webhook exposed by the plugin with the shortcode of the item that was updated, and the plugin will automatically re-sync that item.

**4. Theme Templates**

To display Passle posts and authors, you should create custom templates as part of your theme that include the custom post type names, [as described in the Wordpress documentation](https://developer.wordpress.org/themes/template-files-section/custom-post-type-template-files/). These templates should be called `single-passle-post.php` and `single-passle-author.php`.

This plugin provides the `PasslePost` and `PassleAuthor` helper classes, which you should use in your templates to access the custom Passle fields attached to a post or author more easily. They also ensure you're using the most up to date version of the data available.

For detailed documentation on the available classes, jump to the [API documentation](./docs/index.md).

### 📰 Handling Featured Posts

Post meta is used to identify the featured post, on both the content hub page and the post page, with the keys `post_is_featured_on_passle_page`, and `post_is_featured_on_post_page`.

You can query for featured posts, or exclude them from a query, using one of these values as the meta query `key` parameter, and either `EXISTS` or `NOT EXISTS` as the meta query `compare` parameter.

An example of how to exclude the post featured on the content hub page from your main query can be found in our demo theme's [functions.php](https://github.com/passle/passle-sync-wordpress-demo-theme/blob/master/functions.php#L124).

An example of how to get the content hub featured post in order to display it separately from the rest of the posts can be found in our demo theme's [index.php](https://github.com/passle/passle-sync-wordpress-demo-theme/blob/master/index.php#L9).

## 🔧 Requirements

- Permalinks: Permalink Settings must be set to something other than 'Plain'.
- TBD

## 👨‍💻 Development

<details>
<summary>Prerequisites</summary>

- [NPM](https://www.npmjs.com/)
- [Composer](https://getcomposer.org/)
- Development environment running a Wordpress instance
  - Including a correctly set `/etc/hosts` config.

To build documentation, you will need to ensure `extension=fileinfo` is enabled in your `php.ini`. This extension is disabled by default on Windows.

</details>

<details>
<summary>Environment setup</summary>

To develop this plugin, first clone the repository:

```
git clone https://github.com/passle/passle-sync-wordpress
```

Next, install all dependencies and build the frontend with the following commands:

```
npm install
npm run init
```

</details>

<details>
<summary>Developing the frontend</summary>

To develop the frontend, use the `watch` script available in [frontend/package.json](./frontend/package.json).

</details>

<details>
<summary>Building the plugin zip</summary>

To build the plugin zip file, use the `build:staging` and `build:production` scripts available in [package.json](./package.json). This will install all dependencies (excluding Composer dev dependencies), build the frontend, and create a zip containing all necessary output files.

</details>

<details>
<summary>Generating docs</summary>

Documentation is automatically generated with [phpDocumentor](https://github.com/phpDocumentor/phpDocumentor) and [phpDocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown). To generate documentation, run the `docs` script available in [package.json](./package.json).

</details>

## 💬 Contributing

If you'd like to request a feature or report a bug, please create a GitHub Issue.

## 📜 License

The Passle Sync plugin is released under the under terms of the [MIT License](./LICENSE).
