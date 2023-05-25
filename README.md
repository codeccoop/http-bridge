# WPCT Odoo Connect

## What's this pluggin for?

This plugin connects WordPress with Odoo in a bidirectional way. The idea behind
the plugin is to allow CRUD (create, read, update, and delete) operations between
the two realms.

## How does it work?

The plugin implements GET, POST, PUT & DELETE http methods on php to perform
requests from WP to Odoo. The connection headers are populated with two fields:

1. API-TOKEN: `<odoo-instance-token>`
2. Accept-Language: `<wpml-current-languaga>`

With this two headers, WP can consume the Odoo API with localization.
The `<odoo-instance-token>` is defined on the `settings/wpct-odoo-connect`
as an input field. The `<wpml-current-language>` value is recovered from
the [WPML](https://wpml.org/) plugin.

On the other hand, the plugins has [JWT Authentication for WP REST API plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
as a depedency. On top of this plugin, implements JWT authentication over
the WordPress Rest API that allow Odoo to perform CRUD operations against WP.

JWT Authentication extends the WP user system. This means that Odoo should
know some login credentials to generate access tokens. On install, the
plugin will create a new WP User with login `wpct_oc_user` and password
`wpct_oc_user`. Pleas, remember to change this user password and email to
prevent security breaches.

### **Wordpress REST API**

The WordPress REST API provides an interface for applications to interact with
your WordPress site by sending and receiving data as JSON (JavaScript Object Notation)
objects. IN other words, the REST API allow the same actions user's can perform
from worpress administartion page, but automatized. For more information about
Wordpress REST API see the [official documentation](https://developer.wordpress.org/rest-api/).

### **Wordpress REST API Authentication dependecies**

To support api token based authentication on WordPress, this plugins has
[JWT Authentication for WP REST API plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
as dependency. **JWT Authentication** must be installed along with the plugin. This
plugin extens the WP REST API using JSON Web Tokens Authentication as an
authentication method.

### **Environment variables**

The plugin supports enviroment variable usage as configuration. There are two env
variables:

-   `WPCT_OC_AUTH_SECRET`: A character string to sign the jwt tokens. Default value
    is '123456789'.
-   `WPCT_OC_DEFAULT_LOCALE`: An [ISO 639-1](https://ca.wikipedia.org/wiki/ISO_639-1)
    locale code. Default value is 'ca'.
