import 'dart:async';

import 'package:grpc/grpc.dart';
import 'package:helloworld/src/generated/helloworld.pb.dart';
import 'package:helloworld/src/generated/helloworld.pbgrpc.dart';

Future<void> main(List<String> args) async {
  final channel = ClientChannel('localhost',
      port: 9090,
      options:
          const ChannelOptions(credentials: ChannelCredentials.insecure()));
  final stub = GreeterClient(channel);

  try {
    final response = await stub.sayHello(HelloRequest()..name = 'Siler');
    print('Greeter client received: ${response.message}');
  } catch (e) {
    print('Caught error: $e');
  }
  await channel.shutdown();
}
