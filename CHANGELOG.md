# 1.7.3
- Better error message for `array_get`'s
- Add `array_get_arr` function

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
