# 1.7.8
- GraphQL Annotations for Subscriptions

# 1.7.7
- Allow Config to receive custom Parsers

# 1.7.6
- `nullableList` for GraphQL annotations

# 1.7.5
- Config module

# 1.7.4
- Fix route with URL encoded query strings reported at #354

# 1.7.3
- **GraphQL uploads & custom directives!**
- **GraphQL annotations!**
- GraphiQL
- Enums
- PHPUnit v9
- GraphQL v14
- Introducing Klass module with `Klass\unqualified_name`
- Introducing Obj module with `Obj\patch`
- Introducing `FromArray`, `ToArray` and `Patch`
- Introducing IO module with `println`, `csv_to_array` and `fetch`
- Fix #276 - GraphQL WebSocket client sub-protocol
- Fix #289 - GraphQL Context on subscriptions (thanks @lemonbrain-mk)
- `Siler\array_get_arr` type-safe array getter for arrays
- `Str\starts_with`, `Str\ends_with` and `Str\contains`
- `Str\snake_case` and `Str\camel_case` case converters
- `Str\mb_ucfirst` and `Str\mb_lcfirst` (thanks @enricodias & @williamokano)
- `Prelude\Dispatcher` an Event Dispatcher implementing PSR-14 interfaces
- **Breaking**: `Result` module now adheres to Rust's naming and drops `id`, `code` and `json` support.
  - `Success` is now `Ok`
  - `Failure` is now `Err`
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
