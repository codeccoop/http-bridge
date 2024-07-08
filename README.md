# Wpct Http bridge

## What's this pluggin for?

This plugin connects WordPress with backends in a bidirectional way. The idea behind
the plugin is to allow CRUD (create, read, update, and delete) operations between
the two realms.

## How does it work?

The plugin implements GET, POST, PUT & DELETE http methods on php to perform
requests from WP to any backend. The connection headers are populated with two fields:

1. API-KEY: `<backend-api-key>`
2. Accept-Language: `<wp-current-locale>`

With this two headers, WP can consume the backend's APIs with localization.
The `<backend-api-key>` is defined on the `settings/wpct-http-bridge`
as an input field. The `<wp-current-locale>` value is recovered from
the [Wpct i18n](https://git.coopdevs.org/codeccoop/wp/plugins/wpct-i18n/)
plugin.

The plugin expose the hook `'wpct_http_headers'` as a filter to modify the headers
array before send the request.

On the other hand, the plugins implements JWT authentication over the WordPress Rest
API that allow your backend to perform CRUD operations against WP.

JWT authentication extends the WP user system. This means that the backend should
know some login credentials to generate access tokens.

## Environment variables

The plugin supports enviroment variable usage as configuration.

- `WPCT_HTTP_AUTH_SECRET`: A character string to sign the jwt tokens. Default value
is '123456789'.

## Wordpress REST API

The WordPress REST API provides an interface for applications to interact with
your WordPress site by sending and receiving data as JSON (JavaScript Object Notation)
objects. In other words, the REST API allow the same actions user's can perform
from worpress administartion page, but automatized. For more information about
Wordpress REST API see the [official documentation](https://developer.wordpress.org/rest-api/).

## Endpoints

The plugin register two new endpoints on the `wpct/v1` namespace of the WP REST API.

### Get auth JWT

**URL**: `wp-json/wpct/v1/http-bridge/auth`

**Method**: `POST`

**Authentication**: No

#### Response

**Code**: 200

**Content**:

```json
{
	"token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwiaWF0IjoxNTE2MjM5MDIyLCJleHAiOjE1MTYyMzkwMjIsIm5iZiI6MTUxNjIzOTAyMiwiZGF0YSI6eyJ1c2VyX2lkIjoxfX0.2jlFOg305ui0VTqC-RcoQP8kH7uF5DvW5O-FyNAHTDU",
	"user_login": "admin",
	"user_email": "admin@example.coop",
	"display_name": "Admin"
}
```

#### Bad request

**Condition**: Missing login credentials or not valid JSON payload

**Code**: 400

**Content**:

```json
{
	"code": "rest_bad_request",
	"message": "Missing login credentials",
	"data": {
		"status": 400
	}
}
```

#### Unauthorized

**Condition**: User credentials are invalid

**Code**: 403

**Content**:

```json
{
	"code": "rest_unauthorized",
	"message": "Invalid credentials",
	"data": {
		"status": 403
	}
}
```

#### Token validation

**URL**: `wp-json/wpct/v1/http-bridge/validate`

**Method**: GET

**Authentication**: Bearer authentication

#### Response

**Code**: 200

**Content**:

```json
{
	"token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwiaWF0IjoxNTE2MjM5MDIyLCJleHAiOjE1MTYyMzkwMjIsIm5iZiI6MTUxNjIzOTAyMiwiZGF0YSI6eyJ1c2VyX2lkIjoxfX0.2jlFOg305ui0VTqC-RcoQP8kH7uF5DvW5O-FyNAHTDU",
	"user_login": "admin",
	"user_email": "admin@example.coop",
	"display_name": "Admin"
}
```

#### Unauthorized

**Condition**: Invalid token

**Code**: 403

**Content**:

```json
{
	"code": "rest_unauthorized",
	"message": "Invalid credentials",
	"data": {
		"status": 403
	}
}
```

## Filters

### `wpct_http_headers`

Filter the HTTP headers before each request sent.

Arguments:

1. `array $headers`: Assoicative array with header names and values.
2. `string $method`: Method of the request.
3. `string $url`: URL of the request.

```php
add_filter('wpct_http_headers', function ($headers, $method, $url) {
	return $headers;
}, 10, 3);
```

### `wpct_http_validate_response`

Filters the login data to be returned on REST API requests to the validate endpoint.

Arguments:

1. `array $login_data`: Data to be returned with the auth token and user data.
2. `array $user`: WP_User instance of the authenticated user.

```php
add_filter('wpct_http_validate_response', function ($login_data, $user) {
	return $login_data;
}, 10, 2);
```

### `wpct_http_auth_response`

Filters the login data to be returned on REST API requests to the auth endpoint.

Arguments:

1. `array $login_data`: Data to be returned with the auth token and user data.
2. `array $user`: WP_User instance of the authenticated user.

```php
add_filter('wpct_http_auth_response', function ($login_data, $user) {
	return $login_data;
}, 10, 2);
```

### `wpct_http_auth_expire`

Filters the `exp` claim value of generated JWTs. Default value is `time() + 86400`,
24 hours from now.

Arguments:

1. `int $exp`: PHP timestamp.
2. `int $issuedAt`: PHP timestamp.

```php
add_filter('wpct_http_auth_expire', function ($exp, $issuedAt) {
	return $exp;
}, 10, 2);
```

### `wpct_http_auth_not_before`

Filters the `nbf` claim value of generated JWTs. Default value is `time()`.

Arguments:

1. `int $nbf`: PHP timestamp.
2. `int $issuedAt`: PHP timestamp.

```php
add_filter('wpct_http_auth_not_before', function ($nbf, $issuedAt) {
	return $nbf;
}, 10, 2);
```
