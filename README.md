# HTTP bridge

![HTTP Bridge]()

Connect WP with backends over HTTP.

## What's this pluggin for?

This plugin connects WordPress with backends in a bidirectional way. The idea behind
the plugin is to allow CRUD (create, read, update, and delete) operations between
the two realms.

## How does it work?

The plugin offers a high level API to perform HTTP requests from WordPress, as well as,
a bearer authentication system based on JWT to allow remote source control of the
WordPress instance over its REST API.

The other core feature offered by the plugin is the _backends_ setting with which you
can configure multiple backend connections to connect with and reuse in your instance.
Each backend can be configured with a set of default headers to perform authentication
against the remote server over the HTTP protocol.

> Bearer authentication extends the WP user system. This means that the backend should
> know some login credentials to generate access tokens. If your are concerned about
> security about this login mechanism, you can create custom roles to assign to your
> backend user to limit its capabilities.

## Wordpress REST API

The WordPress REST API provides an interface for applications to interact with
your WordPress site by sending and receiving data as JSON (JavaScript Object
Notation) objects. In other words, the REST API allow the same actions user's
can perform from WordPress administartion page, but automatized. For more information
about Wordpress REST API see the [official documentation](https://developer.wordpress.org/rest-api/).

## Installation

Download the [latest release](https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases/permalink/latest/downloads/plugins/bridges/http-bridge.zip)
as a zipfile. Once downloaded, go to your site plugins page and upload the zip file as a
new plugin, WordPress will handle the rest.

> Go to the [releases](https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases)
> to find previous versions.

If you have access to a console on your server, you can install it with `wp-cli` with the
next command:

```shell
wp plugin install https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge/-/releases/permalink/latest/downloads/plugins/bridges/http-bridge.zip
```

## Getting started

See [install](#install) section to learn how to install the plugin. Once installed,
go to `Settings > HTTP Bridge` to configure your backend connections. The settings
page has two main sections:

1. General
   - **Whitelist backends**: Controls if HTTP Bridge should block incomming connections
     from other sources than the listed on de _backends_ setting.
   - **Backends**: List of configured backend connections. Each backend needs a unique
     name, a base URL, and, optional, a map of HTTP headers.

## Auth secret

To be able to cryptographicaly sign the JWT, HTTP Bridge needs a secret. This secret
should be defined as a const on your code as `HTTP_BRIDGE_AUTH_SECRET`. Default value
is `123456789`.

## Developers

The plugin offers some hooks to expose its internal API. Go to [API](./docs/API.md) to see
more details about the hooks, or to [REST API](./docs/REST-API.md) to see its endpoints.

## Roadmap

1. [ ] Add test coverage with phpunit.
2. [ ] Improve admin client UX.
