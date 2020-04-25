# 1.7.3
- **GraphQL uploads & custom directives!**
- **GraphQL annotations!**
- Enums draft
- PHPUnit 9
- Introducing Klass module with `Klass\unqualified_name`
- Introducing Obj module with `Obj\patch`
- Introducing traits with `FromArray`, `ToArray` and `Patch`
- Introducing IO modile with `println` and `csv_to_array`
- Fix #276 - GraphQL WebSocket client sub-protocol
- Fix #289 - GraphQL Context on subscriptions (thanks @lemonbrain-mk)
- `Siler\array_get_arr` type-safe array getter for arrays
- `Str\starts_with`, `Str\ends_with` and `Str\contains`
- `Str\snake_case` and `Str\camel_case` case converters
- `Str\mb_ucfirst` and `Str\mb_lcfirst` (thanks @enricodias & @williamokano)
- `Arr\assoc` creates an array of associative arrays using the first element as keys
- Fix optional parameters for `Route\class_name`
- Better error message for `array_get`'s
- **Breaking**: you should now explicitly use arrays (or any other type) for subscription's root and context values
- **Breaking**: match doesn't return null anymore, you should provide an exhaust function

# 1.7.2
- Experimental support for gRPC servers
- Support middleware-like pipelines in Swoole with `Swoole\middleware`
- Add `Swoole\redirect()` sugar
- New `Siler\Env` API
- Add `map`, `lmap`, `pipe`, `conduit`, `lconcat`, `ljoin`, `filter` and `lfilter` functions
- Typed array-gets: `array_get_str`, `array_get_int`, `array_get_float` and `array_get_bool`.
- Switch from Zend to Laminas
- Add `Swoole\http2` to create HTTP/2 enabled servers

# 1.7.1
- Fix string callable on route
- Fix trailing separator on `concat`
- Add `lazy` constructor and call_user_func alias `call`

# 1.7.0
- Drops PHP 7.2 support and adds PHP 7.4 support
- Statically-typed Psalm support
- GraphQL Subscriptions with Swoole's WebSocket
- Fix initial data on GraphQL subscriptions
- GraphQL Enum resolvers
- API to enable GraphQL debugging
- API to add custom GraphQL's Promise Executors
- CORS helper for SAPI and Swoole
- Type-safe read ints and bools from environment
- Named route params with Regex validation
- JSON encoding and decoding with opinionated defaults
- No extensions needed on development
- Better API for Maybe Monad
- Monad API for the Result object
- New File module
- Drops Db module
- Drops GraphQL type helpers
