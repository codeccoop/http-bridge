# WPCT Odoo Connect

## **What's this pluggin for?**

This plugin connects WordPress with Odoo in a bidirectional way. The idea behind the plugin is to allow CRUD (create, read, update, and delete) operations between the two realms.

## **How does it work?**

The plugin informs the `Authorization: <odoo-api-token>` and `Accept-Language: <wp-current-lang>` headers on the each http requests made from WordPress to Odoo, intercepted with the custom filter `wpct_forms_set_headers`. The `odoo-api-token` value have to be declared on the admin `settings/wpct-odoo-connect` pannel. The `wp-current-lang` is recovered from the [WMPL](https://wpml.org/) plugin. With this middleware strategy, WordPress can be authorized to perform CRUD operations on the backend.

On the other hand, the plugins installs the [JWT Authentication for WP REST API plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/) plugin to open a new namespace on the **WP Rest API** with multiple endpoints. Throug this new endpoints, Odoo can authenticate and get api keys to perform CRUD operations against WordPress. To achive this, Odoo have to know the username and password of an existing user on the WordPress instance. 

### **Wordpress REST API**

The WordPress REST API provides an interface for applications to interact with your WordPress site by sending and receiving data as JSON (JavaScript Object Notation) objects. IN other words, the REST API allow the same actions user's can perform from worpress administartion page, but automatized.
For more information about Wordpress REST API see the [official documentation](https://developer.wordpress.org/rest-api/).

### **Wordpress REST API Authentication dependecies**

To support api token based authentication on WordPress, this plugins has [JWT Authentication for WP REST API plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/) as  dependency. **JWT Authentication** must be installed along with the plugin. This plugin extens the WP REST API using JSON Web Tokens Authentication as an authentication method.

### **Environment variables**

The plugin supports enviroment variable usage as configuration. There are two env variables:

* `WPCT_OC_AUTH_SECRET`: A character string to sign the jwt tokens. Default value is '123456789'.
* `WPCT_OC_DEFAULT_LOCALE`: An [ISO 639-1](https://ca.wikipedia.org/wiki/ISO_639-1) locale code. Default value is 'ca'.
