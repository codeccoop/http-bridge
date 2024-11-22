# HTTP bridge

![HTTP Bridge]()

Connect WP with backends over HTTP.

## What's this pluggin for?

This plugin connects WordPress with backends in a bidirectional way. The idea behind
the plugin is to allow CRUD (create, read, update, and delete) operations between
the two realms.

## How does it work?

The plugin offers a high level API to perform HTTP requests from wordpress, as well as,
a bearer authentication system to allow remote source control of the wordpress instance
over REST API calls. The core feature is the _backends_ setting with which you
can configure multiple backend connections to connect with in a bidirectional way.

Bearer authentication extends the WP user system. This means that the backend should
know some login credentials to generate access tokens. You can create custom roles
to assign to your backend user to limit its capabilities.

## Wordpress REST API

The WordPress REST API provides an interface for applications to interact with
your WordPress site by sending and receiving data as JSON (JavaScript Object
Notation) objects. In other words, the REST API allow the same actions user's
can perform from worpress administartion page, but automatized. For more information
about Wordpress REST API see the [official documentation](https://developer.wordpress.org/rest-api/).

## Installation

Download the [latest release](https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases/permalink/latest/downloads/plugins/http-bridge.zip)
as a zipfile. Once downloaded, go to your site plugins page and upload the zip file as a
new plugin, WordPress will handle the rest.

> Go to the [releases](https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases)
> to find previous versions.

If you have access to a console on your server, you can install it with `wp-cli` with the
next command:

```shell
wp plugin install https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases/permalink/latest/downloads/plugins/http-bridge.zip
```

## Getting started

See [install](#install) section to learn how to install the plugin. Once installed,
go to `Settings > HTTP Bridge` to configure your backend connections. The settings
page has two main sections:

1. General
	* **Notification receiver**: Email address receiver of failed submission
	notifications.
	* **Backends**: List of configured backend connections. Each backend needs a unique
	name, a base URL, and, optional, a map of HTTP headers.
2. REST API
	* **Form Hooks**: A list of hooked forms and it's relation with your backend
	endpoints. Each relation needs a unique name, a form ID, a backend, and an endpoint.
	Submission will be sent as encoded JSON objects.
3. JSON-RPC API
	* **RPC API endpoint**: Entry point of your ERP JSON-RPC external API.
	* **API user login**: Login of the ERP's user to use on the API authentication
	requests.
	* **User password**: Password of the user.
	* **Database name**: Database  name to be used.
	* **Form Hooks**: A list of hooked forms and it's relation with your backend models.
	Each relation needs a unique name, a from ID, a backend, and a model. Submission
	will be sent encoded as JSON-RPC payloads.

## Environment variables

The plugin supports enviroment variable usage as configuration.

- `HTTP_BRIDGE_AUTH_SECRET`: A character string to sign the jwt tokens.

## Developers

The plugin offers some hooks to expose its internal API. Go to [documentation](./docs/API.md) to see
more details about the hooks. 
