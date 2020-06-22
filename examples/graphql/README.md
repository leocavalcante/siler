# Siler GraphQL Example

This showcases:
- Queries
- Mutations
- Subscriptions (+filters)
- Directives
- File uploads

If you miss something you would like to see how is done with Siler, feel free to request.

- Use the `swoole.php` file to see it working on top of Swoole runtime (Docker compose provided: `docker-compose up`).

- Use `sapi.php` to see how it could be done with regular PHP over (Fast)CGI (`php -S localhost:8000 sapi.php`) *(subscriptions not supported)*.
