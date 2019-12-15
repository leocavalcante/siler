FROM php:7.4

RUN apt-get update && apt-get install -y zlib1g-dev git build-essential autoconf libtool pkg-config

RUN git clone -b v1.25.0 https://github.com/grpc/grpc.git \
  && cd grpc && git submodule update --init

WORKDIR grpc
RUN make

ENV GRPC_ENABLE_FORK_SUPPORT 1

RUN apt-get install -y golang-go \
  && go get -u github.com/golang/protobuf/protoc-gen-go

RUN go get -u google.golang.org/grpc

RUN apt-get install -y apt-transport-https wget gnupg
RUN sh -c 'wget -qO- https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -'
RUN sh -c 'wget -qO- https://storage.googleapis.com/download.dartlang.org/linux/debian/dart_stable.list > /etc/apt/sources.list.d/dart_stable.list'
RUN apt-get update
RUN apt-get install dart
RUN /usr/lib/dart/bin/pub global activate protoc_plugin

RUN touch ~/.bashrc \
  && echo "export GOPATH=\$HOME/go" >> ~/.bashrc \
  && echo "export PATH=\$PATH:/grpc/bins/opt:/grpc/bins/opt/protobuf:\$GOPATH/bin:\$HOME/.pub-cache/bin" >> ~/.bashrc
