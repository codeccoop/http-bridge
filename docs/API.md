# API

## Table of Contents

1. [Methods](#Methods)
2. [Getters](#getters)
3. [Filters](#filters)
4. [Actions](#actions)

## Methods

All plugin public methods can be proxies with actions like

```php
do_action(`{method_name}`, ...$args);
```

This API prevent errors on theme functions when the plugin is not installed.

### `http_bridge_get`

Performs GET requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $params`: URL query params, empty array as default.
3. `array $headers`: HTTP headers, empty array as default.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_get('https://example.coop/api/echo', ['foo' => 'bar']);
```

### `http_bridge_post`

Performs POST requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $data`: Request payload.
3. `array $headers`: HTTP headers, empty array as default.
4. `array $files`: Array with files, empty array as default.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_post(
	'https://example.coop/api/echo',
	['foo' => 'bar'],
	['Content-Type' => 'application/x-www-form-urlencoded']
);
```

### `http_bridge_put`

Performs PUT requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $data`: Request payload.
3. `array $headers`: HTTP headers, empty array as default.
4. `array $files`: Array with files, empty array as default.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_put(
	'https://example.coop/api/echo',
	['foo' => 'bar'],
	['Authorization' => 'Bearer 53CR37-70K3N'],
	['image' => '/path/to/file.img'],
]);
```

### `http_bridge_delete`

Performs DELETE requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $params`: URL query params, empty array as default.
3. `array $headers`: HTTP headers, empty array as default.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_delete('https://example.coop/api/echo');
```

## Getters

### `http_bridge_backend`

Get a backend instance by name.

#### Arguments

1. `mixed $default`: Fallback value.
2. `string $name`: Backend name.

#### Returns

1. `array|null $backend`: Backend instance.

#### Example

```php
$backend = apply_filter('http_bridge_backend', null, 'Odoo');
if (!empty($backend)) {
	// do something
}
```

### `http_bridge_backends`

Get configured backends instances.

#### Arguments

1. `mixed $default`: Fallback value.

#### Returns

1. `array $backends`: List of available backend instances.

#### Example

```php
$backends = apply_filters('http_bridge_backends', []);
foreach ($backends as $backend) {
	// do something
}
```

## Filters

### `http_bridge_backend_url`

Filter the HTTP request arguments before is sent.

#### Arguments

1. `string $url`: Array with `url`, `method`, `headers`, `files`, `params` and `data` keys.
2. `Http_Backend $backend`: Instance of the current backend object.

#### Example

```php
add_filter('http_bridge_backend_url', function ($url, $backend) {
	return $url;
}, 10, 2);
```

### `http_bridge_backend_headers`

Filters the HTTP headers the backend will be set to its HTTP connexions.

#### Arguments

1. `array $headers`: Default backend's HTTP headers.
2. `Http_Backend $backend`: Instance of the current backend object.

#### Example

```php
add_filter('http_bridge_backend_headers', function ($headers, $backend) {
	return $headers;
}, 10, 2);
```

### `http_bridge_validate_response`

Filters the login data to be returned on REST API requests to the validate endpoint.

#### Arguments

1. `array $login_data`: Data to be returned with the auth token and user data.
2. `array $user`: WP_User instance of the authenticated user.

#### Example

```php
add_filter('http_bridge_validate_response', function ($login_data, $user) {
	return $login_data;
}, 10, 2);
```

### `http_bridge_auth_response`

Filters the login data to be returned on REST API requests to the auth endpoint.

#### Arguments

1. `array $login_data`: Data to be returned with the auth token and user data.
2. `array $user`: WP_User instance of the authenticated user.

#### Example

```php
add_filter('http_bridge_auth_response', function ($login_data, $user) {
	return $login_data;
}, 10, 2);
```

### `http_bridge_auth_expire`

Filters the `exp` claim value of generated JWTs. Default value is `time() + 86400`,
24 hours from now.

#### Arguments

1. `int $exp`: PHP timestamp.
2. `int $issuedAt`: PHP timestamp.

#### Example

```php
add_filter('http_bridge_auth_expire', function ($exp, $issuedAt) {
	return $exp;
}, 10, 2);
```

### `http_bridge_auth_not_before`

Filters the `nbf` claim value of generated JWTs. Default value is `time()`.

#### Arguments

1. `int $nbf`: PHP timestamp.
2. `int $issuedAt`: PHP timestamp.

#### Example

```php
add_filter('http_bridge_auth_not_before', function ($nbf, $issuedAt) {
	return $nbf;
}, 10, 2);
```

## Actions

### `http_bridge_before_request`

Fired before each time Http Bridge opens a new HTTP request.

#### Arguments

1. `array $request`: Request data.

#### Example

```php
add_action('http_bridge_before_request', function ($request) {
	if ($request['url'] === 'https://example.coop') {
		// do something
	}
});
```

### `http_bridge_after_request`

#### Arguments

1. `array|WP_Error $response`: HTTP request response, or error.
2. `array $request`: Request data.

#### Example

```php
add_action('http_bridge_after_request', function ($response, $request) {
	if ($response['headers']['x-wp-totalpages'] > 1) {
		 // do something
	}
}, 10, 2);
```
