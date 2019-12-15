Docker-powered `protoc` and gRPC plugins.

```powershell
docker build -t proto_gen .
```

```powershell
docker run --rm -v "$(pwd):/gen" -w /gen proto_gen bash -l ./proto_gen.sh
```
