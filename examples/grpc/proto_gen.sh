#!/usr/bin/env bash
protoc \
  --php_out=clients/php/src --grpc_out=clients/php/src \
  --plugin=protoc-gen-grpc=/grpc/bins/opt/grpc_php_plugin \
  -Iprotos helloworld.proto

protoc \
  --go_out=plugins=grpc:clients/go \
  -Iprotos helloworld.proto

protoc \
  --dart_out=grpc:clients/dart/lib/src/generated \
  -Iprotos helloworld.proto

protoc \
  --js_out=import_style=commonjs,binary:clients/node --grpc_out=clients/node \
  --plugin=protoc-gen-grpc=/grpc/bins/opt/grpc_node_plugin \
  -Iprotos helloworld.proto
