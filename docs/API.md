# API

## Getters

### `http_bridge_backend`

Get backend configuration by name.

#### Arguments

1. `any $default`: Default value.
2. `string $name`: Backend name.

#### Returns

1. `array|null $backend`: Backend data.

#### Example

```php
$backend = apply_filter('http_bridge_backend', null, 'Odoo');
if (!empty($backend)) {
	// do something
}
```

### `http_bridge_backends`

Get configured backends list.

#### Arguments

1. `any $default`: Default value.

#### Returns

1. `array $backends`: List of available backends.

#### Example

```php
$backends = apply_filters('http_bridge_backends', []);
foreach ($backends as $backend) {
	// do something
}
```

## Methods

### `http_bridge_get`

Performs GET requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_get('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'params' => [
		'foo' => 'bar',
	],
]);
```

### `http_bridge_post`

Performs POST requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_post('https://example.coop/api/echo', [
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

### `http_bridge_put`

Performs PUT requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_put('https://example.coop/api/echo', [
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

### `http_bridge_delete`

Performs DELETE requests.

#### Arguments

1. `string $url`: Target URL.
2. `array $args`: Request arguments.

#### Returns

1. `array|WP_Error`: Request response.

#### Example

```php
$response = http_bridge_delete('https://example.coop/api/echo', [
	'headers' => [
		'Accept': 'application/json'
	],
	'params' => [
		'foo' => 'bar',
	],
]);
```

## Filters

### `http_bridge_request_args`

Filter the HTTP request arguments before is sent.

#### Arguments

1. `array $args`: Array with `url`, `method`, `headers`, `files`, `params` and `data` keys.

#### Example

```php
add_filter('http_bridge_req_args', function ($args) {
	return $args;
}, 10, 3);
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
