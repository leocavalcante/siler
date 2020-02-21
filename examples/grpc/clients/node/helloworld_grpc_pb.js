// GENERATED CODE -- DO NOT EDIT!

'use strict';
var grpc = require('grpc');
var helloworld_pb = require('./helloworld_pb.js');

function serialize_helloworld_HelloReply(arg) {
  if (!(arg instanceof helloworld_pb.HelloReply)) {
    throw new Error('Expected argument of type helloworld.HelloReply');
  }
  return new Buffer(arg.serializeBinary());
}

function deserialize_helloworld_HelloReply(buffer_arg) {
  return helloworld_pb.HelloReply.deserializeBinary(new Uint8Array(buffer_arg));
}

function serialize_helloworld_HelloRequest(arg) {
  if (!(arg instanceof helloworld_pb.HelloRequest)) {
    throw new Error('Expected argument of type helloworld.HelloRequest');
  }
  return new Buffer(arg.serializeBinary());
}

function deserialize_helloworld_HelloRequest(buffer_arg) {
  return helloworld_pb.HelloRequest.deserializeBinary(new Uint8Array(buffer_arg));
}


var GreeterService = exports.GreeterService = {
  sayHello: {
    path: '/helloworld.Greeter/sayHello',
    requestStream: false,
    responseStream: false,
    requestType: helloworld_pb.HelloRequest,
    responseType: helloworld_pb.HelloReply,
    requestSerialize: serialize_helloworld_HelloRequest,
    requestDeserialize: deserialize_helloworld_HelloRequest,
    responseSerialize: serialize_helloworld_HelloReply,
    responseDeserialize: deserialize_helloworld_HelloReply,
  },
};

exports.GreeterClient = grpc.makeGenericClientConstructor(GreeterService);
