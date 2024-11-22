# Endpoints

The plugin register two new endpoints on the `wp-bridges/v1` namespace of the WP REST API.

## Get auth JWT

**URL**: `/wp-json/wp-bridges/v1/http/auth`

**Method**: `POST`

**Body**:

```json
{
  "username": "admin",
  "password": "admin"
}
```

**Authentication**: No

### Response

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

### Bad request

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

### Unauthorized

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

## Token validation

**URL**: `/wp-json/wp-bridges/v1/http/validate-token`

**Method**: GET

**Authentication**: Bearer authentication

### Response

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

### Unauthorized

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
