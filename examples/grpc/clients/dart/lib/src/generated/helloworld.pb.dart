///
//  Generated code. Do not modify.
//  source: helloworld.proto
//
// @dart = 2.3
// ignore_for_file: camel_case_types,non_constant_identifier_names,library_prefixes,unused_import,unused_shown_name,return_of_invalid_type

import 'dart:core' as $core;

import 'package:protobuf/protobuf.dart' as $pb;

class HelloRequest extends $pb.GeneratedMessage {
  static final $pb.BuilderInfo _i = $pb.BuilderInfo('HelloRequest',
      package: const $pb.PackageName('helloworld'), createEmptyInstance: create)
    ..aOS(1, 'name')
    ..hasRequiredFields = false;

  HelloRequest._() : super();

  factory HelloRequest() => create();

  factory HelloRequest.fromBuffer($core.List<$core.int> i,
          [$pb.ExtensionRegistry r = $pb.ExtensionRegistry.EMPTY]) =>
      create()..mergeFromBuffer(i, r);

  factory HelloRequest.fromJson($core.String i,
          [$pb.ExtensionRegistry r = $pb.ExtensionRegistry.EMPTY]) =>
      create()..mergeFromJson(i, r);

  HelloRequest clone() => HelloRequest()..mergeFromMessage(this);

  HelloRequest copyWith(void Function(HelloRequest) updates) =>
      super.copyWith((message) => updates(message as HelloRequest));

  $pb.BuilderInfo get info_ => _i;

  @$core.pragma('dart2js:noInline')
  static HelloRequest create() => HelloRequest._();

  HelloRequest createEmptyInstance() => create();

  static $pb.PbList<HelloRequest> createRepeated() =>
      $pb.PbList<HelloRequest>();

  @$core.pragma('dart2js:noInline')
  static HelloRequest getDefault() => _defaultInstance ??=
      $pb.GeneratedMessage.$_defaultFor<HelloRequest>(create);
  static HelloRequest _defaultInstance;

  @$pb.TagNumber(1)
  $core.String get name => $_getSZ(0);

  @$pb.TagNumber(1)
  set name($core.String v) {
    $_setString(0, v);
  }
  @$pb.TagNumber(1)
  $core.bool hasName() => $_has(0);
  @$pb.TagNumber(1)
  void clearName() => clearField(1);
}

class HelloReply extends $pb.GeneratedMessage {
  static final $pb.BuilderInfo _i = $pb.BuilderInfo(
      'HelloReply', package: const $pb.PackageName('helloworld'),
      createEmptyInstance: create)
    ..aOS(1, 'message')
    ..hasRequiredFields = false
  ;

  HelloReply._() : super();

  factory HelloReply() => create();

  factory HelloReply.fromBuffer($core.List<$core.int> i,
      [$pb.ExtensionRegistry r = $pb.ExtensionRegistry.EMPTY]) =>
      create()
        ..mergeFromBuffer(i, r);

  factory HelloReply.fromJson($core.String i,
      [$pb.ExtensionRegistry r = $pb.ExtensionRegistry.EMPTY]) =>
      create()
        ..mergeFromJson(i, r);

  HelloReply clone() =>
      HelloReply()
        ..mergeFromMessage(this);

  HelloReply copyWith(void Function(HelloReply) updates) =>
      super.copyWith((message) => updates(message as HelloReply));

  $pb.BuilderInfo get info_ => _i;

  @$core.pragma('dart2js:noInline')
  static HelloReply create() => HelloReply._();

  HelloReply createEmptyInstance() => create();

  static $pb.PbList<HelloReply> createRepeated() => $pb.PbList<HelloReply>();

  @$core.pragma('dart2js:noInline')
  static HelloReply getDefault() => _defaultInstance ??=
      $pb.GeneratedMessage.$_defaultFor<HelloReply>(create);
  static HelloReply _defaultInstance;

  @$pb.TagNumber(1)
  $core.String get message => $_getSZ(0);

  @$pb.TagNumber(1)
  set message($core.String v) {
    $_setString(0, v);
  }
  @$pb.TagNumber(1)
  $core.bool hasMessage() => $_has(0);
  @$pb.TagNumber(1)
  void clearMessage() => clearField(1);
}

