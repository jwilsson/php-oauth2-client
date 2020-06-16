# Token
Each call to a grant's `requestAccessToken()` method returns a `Token` instance after a successful request.

## Methods
Each `Token` instance exposes the following methods.

### getAccessToken()
Returns an access token.

### getExpires()
Returns the token's expiration time in seconds since the Unix Epoch.

### getRefreshToken()
Returns an refresh token.

### getScope()
Returns the scope associated with a token.

### getTokenType()
Returns the token type associated with a token.

### getValues()
Returns an array of other values associated with a token.

## Storing and retrieving token values
Each `Token` instance also implements the [JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php) interface, allowing you to call `json_encode()` directly on an instance and get a JSON object to store the data for later use.

```php
$tokenJson = json_encode($token); // '{"access_token":"abc...","refresh_token":"123..."}'
```

To create a `Token` instance from a previously stored value, call `json_decode()` on the data and pass it to the `Token` constructor.

```php
$tokenData = json_decode($tokenJson, true); // Make sure is's an associative array

$token = new OAuth2\Token($tokenData);
```
