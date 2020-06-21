<?php
/**
 * @noinspection PhpConstantNamingConventionInspection
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace Siler\Container;
const get = '\Siler\Container\get';
const set = '\Siler\Container\set';
const has = '\Siler\Container\has';
const clear = '\Siler\Container\clear';
const inject = '\Siler\Container\inject';
const retrieve = '\Siler\Container\retrieve';


namespace Siler\Diactoros;
const request = '\Siler\Diactoros\request';
const response = '\Siler\Diactoros\response';
const html = '\Siler\Diactoros\html';
const json = '\Siler\Diactoros\json';
const text = '\Siler\Diactoros\text';
const redirect = '\Siler\Diactoros\redirect';
const none = '\Siler\Diactoros\none';


namespace Siler\Dotenv;
const init = '\Siler\Dotenv\init';
const env = '\Siler\Dotenv\env';
const int_val = '\Siler\Dotenv\int_val';
const bool_val = '\Siler\Dotenv\bool_val';
const requires = '\Siler\Dotenv\requires';


namespace Siler\Encoder\Json;
const encode = '\Siler\Encoder\Json\encode';
const decode = '\Siler\Encoder\Json\decode';


namespace Siler\Env;
const env_var = '\Siler\Env\env_var';
const env_int = '\Siler\Env\env_int';
const env_bool = '\Siler\Env\env_bool';


namespace Siler\File;
const recursively_iterated_directory = '\Siler\File\recursively_iterated_directory';
const recur_iter_dir = '\Siler\File\recur_iter_dir';
const concat_files = '\Siler\File\concat_files';
const join_dir = '\Siler\File\join_dir';


namespace Siler\Functional;
const identity = '\Siler\Functional\identity';
const always = '\Siler\Functional\always';
const equal = '\Siler\Functional\equal';
const less_than = '\Siler\Functional\less_than';
const greater_than = '\Siler\Functional\greater_than';
const if_else = '\Siler\Functional\if_else';
const match = '\Siler\Functional\match';
const any = '\Siler\Functional\any';
const all = '\Siler\Functional\all';
const not = '\Siler\Functional\not';
const add = '\Siler\Functional\add';
const mul = '\Siler\Functional\mul';
const sub = '\Siler\Functional\sub';
const div = '\Siler\Functional\div';
const mod = '\Siler\Functional\mod';
const compose = '\Siler\Functional\compose';
const bool = '\Siler\Functional\bool';
const noop = '\Siler\Functional\noop';
const hold = '\Siler\Functional\hold';
const puts = '\Siler\Functional\puts';
const flatten = '\Siler\Functional\flatten';
const head = '\Siler\Functional\head';
const last = '\Siler\Functional\last';
const tail = '\Siler\Functional\tail';
const init = '\Siler\Functional\init';
const uncons = '\Siler\Functional\uncons';
const non_null = '\Siler\Functional\non_null';
const non_empty = '\Siler\Functional\non_empty';
const partial = '\Siler\Functional\partial';
const if_then = '\Siler\Functional\if_then';
const is_empty = '\Siler\Functional\is_empty';
const isnull = '\Siler\Functional\isnull';
const concat = '\Siler\Functional\concat';
const lazy = '\Siler\Functional\lazy';
const call = '\Siler\Functional\call';
const map = '\Siler\Functional\map';
const lmap = '\Siler\Functional\lmap';
const pipe = '\Siler\Functional\pipe';
const conduit = '\Siler\Functional\conduit';
const lconcat = '\Siler\Functional\lconcat';
const ljoin = '\Siler\Functional\ljoin';
const filter = '\Siler\Functional\filter';
const lfilter = '\Siler\Functional\lfilter';


namespace Siler\Functional\Monad;
const identity = '\Siler\Functional\Monad\identity';
const maybe = '\Siler\Functional\Monad\maybe';


namespace Siler\GraphQL;
const debug = '\Siler\GraphQL\debug';
const debugging = '\Siler\GraphQL\debugging';
const init = '\Siler\GraphQL\init';
const input = '\Siler\GraphQL\input';
const request = '\Siler\GraphQL\request';
const execute = '\Siler\GraphQL\execute';
const promise_execute = '\Siler\GraphQL\promise_execute';
const psr7 = '\Siler\GraphQL\psr7';
const schema = '\Siler\GraphQL\schema';
const resolvers = '\Siler\GraphQL\resolvers';
const directives = '\Siler\GraphQL\directives';
const subscriptions_manager = '\Siler\GraphQL\subscriptions_manager';
const subscriptions = '\Siler\GraphQL\subscriptions';
const subscriptions_at = '\Siler\GraphQL\subscriptions_at';
const publish = '\Siler\GraphQL\publish';
const listen = '\Siler\GraphQL\listen';
const annotated = '\Siler\GraphQL\annotated';


namespace Siler\Grpc;
const server = '\Siler\Grpc\server';
const finisher = '\Siler\Grpc\finisher';


namespace Siler\Http;
const cookie = '\Siler\Http\cookie';
const session = '\Siler\Http\session';
const setsession = '\Siler\Http\setsession';
const flash = '\Siler\Http\flash';
const redirect = '\Siler\Http\redirect';
const url = '\Siler\Http\url';
const path = '\Siler\Http\path';
const uri = '\Siler\Http\uri';


namespace Siler\Http\Request;
const raw = '\Siler\Http\Request\raw';
const params = '\Siler\Http\Request\params';
const json = '\Siler\Http\Request\json';
const body_parse = '\Siler\Http\Request\body_parse';
const is_json = '\Siler\Http\Request\is_json';
const is_multipart = '\Siler\Http\Request\is_multipart';
const content_type = '\Siler\Http\Request\content_type';
const headers = '\Siler\Http\Request\headers';
const header = '\Siler\Http\Request\header';
const get = '\Siler\Http\Request\get';
const post = '\Siler\Http\Request\post';
const input = '\Siler\Http\Request\input';
const file = '\Siler\Http\Request\file';
const method = '\Siler\Http\Request\method';
const method_is = '\Siler\Http\Request\method_is';
const accepted_locales = '\Siler\Http\Request\accepted_locales';
const recommended_locale = '\Siler\Http\Request\recommended_locale';
const bearer = '\Siler\Http\Request\bearer';
const authorization_header = '\Siler\Http\Request\authorization_header';


namespace Siler\Http\Response;
const output = '\Siler\Http\Response\output';
const text = '\Siler\Http\Response\text';
const html = '\Siler\Http\Response\html';
const jsonstr = '\Siler\Http\Response\jsonstr';
const json = '\Siler\Http\Response\json';
const header = '\Siler\Http\Response\header';
const redirect = '\Siler\Http\Response\redirect';
const no_content = '\Siler\Http\Response\no_content';
const cors = '\Siler\Http\Response\cors';
const not_found = '\Siler\Http\Response\not_found';


namespace Siler\HttpHandlerRunner;
const sapi_emit = '\Siler\HttpHandlerRunner\sapi_emit';


namespace Siler\SwiftMailer;
const send = '\Siler\SwiftMailer\send';
const message = '\Siler\SwiftMailer\message';
const smtp = '\Siler\SwiftMailer\smtp';
const mailer = '\Siler\SwiftMailer\mailer';


namespace Siler\Monolog;
const stream = '\Siler\Monolog\stream';
const log = '\Siler\Monolog\log';
const log_if = '\Siler\Monolog\log_if';
const handler = '\Siler\Monolog\handler';
const debug = '\Siler\Monolog\debug';
const debug_if = '\Siler\Monolog\debug_if';
const info = '\Siler\Monolog\info';
const notice = '\Siler\Monolog\notice';
const warning = '\Siler\Monolog\warning';
const error = '\Siler\Monolog\error';
const critical = '\Siler\Monolog\critical';
const alert = '\Siler\Monolog\alert';
const emergency = '\Siler\Monolog\emergency';


namespace Siler\Arr;
const set = '\Siler\Arr\set';
const assoc = '\Siler\Arr\assoc';


namespace Siler\IO;
const println = '\Siler\IO\println';
const csv_to_array = '\Siler\IO\csv_to_array';


namespace Siler\Klass;
const unqualified_name = '\Siler\Klass\unqualified_name';


namespace Siler\Obj;
const patch = '\Siler\Obj\patch';


namespace Siler\Str;
const slugify = '\Siler\Str\slugify';
const lines = '\Siler\Str\lines';
const starts_with = '\Siler\Str\starts_with';
const ends_with = '\Siler\Str\ends_with';
const contains = '\Siler\Str\contains';
const snake_case = '\Siler\Str\snake_case';
const camel_case = '\Siler\Str\camel_case';
const mb_ucfirst = '\Siler\Str\mb_ucfirst';
const mb_lcfirst = '\Siler\Str\mb_lcfirst';


namespace Siler\Tuple;
const tuple = '\Siler\Tuple\tuple';


namespace Siler\Ratchet;
const graphql_subscriptions = '\Siler\Ratchet\graphql_subscriptions';


namespace Siler\Result;
const success = '\Siler\Result\success';
const failure = '\Siler\Result\failure';


namespace Siler\Route;
const get = '\Siler\Route\get';
const post = '\Siler\Route\post';
const put = '\Siler\Route\put';
const delete = '\Siler\Route\delete';
const options = '\Siler\Route\options';
const any = '\Siler\Route\any';
const route = '\Siler\Route\route';
const method_path = '\Siler\Route\method_path';
const regexify = '\Siler\Route\regexify';
const resource = '\Siler\Route\resource';
const routify = '\Siler\Route\routify';
const files = '\Siler\Route\files';
const class_name = '\Siler\Route\class_name';
const stop_propagation = '\Siler\Route\stop_propagation';
const cancel = '\Siler\Route\cancel';
const canceled = '\Siler\Route\canceled';
const resume = '\Siler\Route\resume';
const match = '\Siler\Route\match';
const did_match = '\Siler\Route\did_match';
const purge_match = '\Siler\Route\purge_match';
const base = '\Siler\Route\base';


namespace Siler;
const array_get = '\Siler\array_get';
const array_get_str = '\Siler\array_get_str';
const array_get_int = '\Siler\array_get_int';
const array_get_float = '\Siler\array_get_float';
const array_get_bool = '\Siler\array_get_bool';
const array_get_arr = '\Siler\array_get_arr';
const require_fn = '\Siler\require_fn';


namespace Siler\Stratigility;
const process = '\Siler\Stratigility\process';
const pipe = '\Siler\Stratigility\pipe';
const handle = '\Siler\Stratigility\handle';


namespace Siler\Swoole;
const http_handler = '\Siler\Swoole\http_handler';
const http = '\Siler\Swoole\http';
const http2 = '\Siler\Swoole\http2';
const request = '\Siler\Swoole\request';
const response = '\Siler\Swoole\response';
const emit = '\Siler\Swoole\emit';
const json = '\Siler\Swoole\json';
const websocket_hooks = '\Siler\Swoole\websocket_hooks';
const websocket = '\Siler\Swoole\websocket';
const push = '\Siler\Swoole\push';
const broadcast = '\Siler\Swoole\broadcast';
const cors = '\Siler\Swoole\cors';
const raw = '\Siler\Swoole\raw';
const no_content = '\Siler\Swoole\no_content';
const not_found = '\Siler\Swoole\not_found';
const graphql_subscriptions = '\Siler\Swoole\graphql_subscriptions';
const bearer = '\Siler\Swoole\bearer';
const http_server_port = '\Siler\Swoole\http_server_port';
const graphql_handler = '\Siler\Swoole\graphql_handler';
const redirect = '\Siler\Swoole\redirect';
const middleware = '\Siler\Swoole\middleware';


namespace Siler\Twig;
const init = '\Siler\Twig\init';
const render = '\Siler\Twig\render';

