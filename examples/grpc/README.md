Docker-powered `protoc` and gRPC plugins.

```bash
docker build -t proto_gen .
```

```bash
docker run --rm -v $(pwd):/gen -w /gen proto_gen sh ./proto_gen.sh
```
