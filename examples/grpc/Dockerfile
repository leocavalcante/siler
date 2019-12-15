FROM debian:buster-slim

RUN apt-get update && apt-get install -y \
  software-properties-common zlib1g-dev git build-essential autoconf libtool pkg-config apt-transport-https wget gnupg2

RUN add-apt-repository ppa:longsleep/golang-backports \
  && apt-get update && apt-get install -y golang-go \
  && go get -u google.golang.org/grpc \
  && go get -u github.com/golang/protobuf/protoc-gen-go

RUN sh -c 'wget -qO- https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -' \
  && sh -c 'wget -qO- https://storage.googleapis.com/download.dartlang.org/linux/debian/dart_stable.list > /etc/apt/sources.list.d/dart_stable.list' \
  && apt-get update && apt-get install -y dart \
  &&  /usr/lib/dart/bin/pub global activate protoc_plugin

RUN git clone -b v1.25.0 https://github.com/grpc/grpc.git \
  && cd grpc && git submodule update --init && make

ENV GOPATH $HOME/go
ENV PATH /grpc/bins/opt:/grpc/bins/opt/protobuf:$GOPATH/bin:$HOME/.pub-cache/bin:$PATH
