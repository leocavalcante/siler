const grpc = require('grpc');

const messages = require('./helloworld_pb');
const services = require('./helloworld_grpc_pb');

const client = new services.GreeterClient('localhost:9090',
  grpc.credentials.createInsecure());

const request = new messages.HelloRequest();
request.setName('Siler');

client.sayHello(request, function (err, response) {
  console.log('Greeting:', response.getMessage());
});
