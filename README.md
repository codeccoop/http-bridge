# Wpct Http bridge

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

## Environment variables

The plugin supports enviroment variable usage as configuration.

- `WPCT_HTTP_AUTH_SECRET`: A character string to sign the jwt tokens.

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

## API

### Getters

#### `wpct_http_backend`

Get backend configuration by name.

Arguments:

1. `any $default`: Default value.
2. `string $name`: Backend name.

Returns:

1. `array|null $backend`: Backend data.

Example:

```php
$backend = apply_filter('wpct_http_backend', null, 'Odoo');
if (!empty($backend)) {
	// do something
}
```

#### `wpct_http_backends`

Get configured backends list.

Arguments:

1. `any $default`: Default value.

Returns:

1. `array $backends`: List of available backends.

Example:

```php
$backends = apply_filters('wpct_http_backends', []);
foreach ($backends as $backend) {
	// do something
}
```

### Methods

#### `wpct_http_get`

Performs GET requests.

Arguments:

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

Returns:

1. `array|WP_Error`: Request response.

Example:

```php
$response = wpct_http_get('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'params' => [
		'foo' => 'bar',
	],
]);
```

#### `wpct_http_post`

Performs POST requests.

Arguments:

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

Returns:

1. `array|WP_Error`: Request response.

Example:

```php
$response = wpct_http_post('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'data' => [
		'foo' => 'bar',
	],
	'files' => [
		'file-name' => '/path/to/file.ext',
	],
]);
```

#### `wpct_http_put`

Performs PUT requests.

Arguments:

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

Returns:

1. `array|WP_Error`: Request response.

Example:

```php
$response = wpct_http_put('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'data' => [
		'foo' => 'bar',
	],
	'files' => [
		'file-name' => '/path/to/file.ext',
	],
]);
```

#### `wpct_http_delete`

Performs DELETE requests.

Arguments:

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

Returns:

1. `array|WP_Error`: Request response.

Example:

```php
$response = wpct_http_delete('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'params' => [
		'foo' => 'bar',
	],
]);
```

### Filters

#### `wpct_http_request_args`

Filter the HTTP request arguments before is sent.

Arguments:

1. `array $args`: Array with `url`, `method`, `headers`, `files`, `params` and `data` keys.

```php
add_filter('wpct_http_req_args', function ($args) {
	return $args;
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
