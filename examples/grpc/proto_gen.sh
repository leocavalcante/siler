#!/usr/bin/env bash
protoc --proto_path=./ \
       --php_out=./src \
       --grpc_out=./src \
       --plugin=protoc-gen-grpc=./../../../../grpc/bins/opt/grpc_php_plugin \
       ./helloworld.proto
