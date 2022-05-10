# Passle Sync for Wordpress

Passle Sync is a plugin for Wordpress which syncs your [Passle](https://home.passle.net/) posts and authors into your Wordpress instance.

Get started with the section below, or jump straight to the [API documentation](./docs/index.md).

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

First, the plugin has to fetch posts and authors from the Passle API. Use the **Refresh Posts** and **Refresh People** buttons to do so. Once the plugin has done the initial fetches from the API, the API responses will be cached, so if you reload the page, the posts and authors you have fetched will be remembered.

**2. Sync to Wordpress**

To sync the posts and and authors to Wordpress, use the **Sync All Posts** and **Sync All People** buttons. This will create a new Wordpress post for each post/author under a custom post type. Once all posts/authors have been synced, their statuses will update. Synced posts and authors can be viewed, but not edited, under the Passle Posts and Passle Authors menu items in the sidebar.

**3. Webhooks**

Whenever a post or author is updated through the Passle interface, the Passle backend will make a call to a webhook exposed by the plugin with the shortcode of the item that was updated, and the plugin will automatically re-sync that item.

**4. Theme Templates**

To display Passle posts and authors, you should create custom templates as part of your theme that include the custom post type names, [as described in the Wordpress documentation](https://developer.wordpress.org/themes/template-files-section/custom-post-type-template-files/). These templates should be called `single-passle-post.php` and `single-passle-author.php`.

This plugin provides the `PasslePost` and `PassleAuthor` helper classes, which you should use in your templates to access the custom Passle fields attached to a post or author more easily. They also ensure you're using the most up to date version of the data available.

For detailed documentation on the available classes, jump to the [API documentation](./docs/index.md).

## 🔧 Requirements

TBD

## 👨‍💻 Development

<details>
<summary>Prerequisites</summary>

You will need a development environment running a Wordpress instance.

To build documentation, you will need to ensure `extension=fileinfo` is enabled in your `php.ini`. This extension is disabled by default on Windows.

</details>

<details>
<summary>Environment setup</summary>

To develop this plugin, first clone the repository:

```
git clone https://github.com/passle/passle-sync-wordpress
```

Then install all dependencies with [Composer](https://getcomposer.org/):

```
composer install
```

To build the frontend, use the `watch` and `build` scripts availabile in `frontend/package.json`.

</details>

<details>
<summary>Generating docs</summary>

Documentation is automatically generated with [phpDocumentor](https://github.com/phpDocumentor/phpDocumentor) and [phpDocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown). To generate documentation, run the Composer script:

```
composer run generate-docs
```

</details>

## 💬 Contributing

If you'd like to request a feature or report a bug, please create a GitHub Issue.

## 📜 License

The Passle Sync plugin is released under the under terms of the [MIT License](./LICENSE).
