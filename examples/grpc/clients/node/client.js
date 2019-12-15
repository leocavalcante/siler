const client = new hello_proto.Greeter('localhost:9090', grpc.credentials.createInsecure());

client.sayHello({name: 'you'}, function (err, response) {
  console.log('Greeting:', response.message);
});
client.sayHelloAgain({name: 'you'}, function (err, response) {
  console.log('Greeting:', response.message);
});
