<?php declare(strict_types=1);

namespace Siler\Grpc;

use Exception;
use Google\Protobuf\Internal\Message;
use ReflectionClass;

class Parser
{

    public static function serialize(Message $message): string
    {
        return self::pack($message->serializeToString());
    }

    public static function pack(string $data): string
    {
        return $data = pack('CN', 0, strlen($data)) . $data;
    }

    /**
     * @param ReflectionClass $message_class
     * @param string $value
     * @return Message|null
     * @throws Exception
     */
    public static function deserialize(ReflectionClass $message_class, string $value): ?Message
    {
        if (empty($value)) {
            return null;
        }

        $value = self::unpack($value);

        /** @var Message $object */
        $object = $message_class->newInstance();
        $object->mergeFromString($value);

        return $object;
    }

    public static function unpack(string $data): string
    {
        // it's the way to verify the package length
        // 1 + 4 + data
        // $len = unpack('N', substr($data, 1, 4))[1];
        // assert(strlen($data) - 5 === $len);
        return $data = substr($data, 5);
    }
}
