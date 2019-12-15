#!/usr/bin/env bash
protoc \
  --php_out=clients/php/src --grpc_out=clients/php/src --plugin=protoc-gen-grpc=/grpc/bins/opt/grpc_php_plugin \
  --go_out=plugins=grpc:clients/go \
  --dart_out=grpc:clients/dart/lib/src/generated \
  -Iprotos helloworld.proto
