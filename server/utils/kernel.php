<?php declare(strict_types=1);

use Psr\Http\Message\MessageInterface;

if (!function_exists('config_get')) {
    function config_get(?string $key = null, mixed $default = null): mixed
    {
        if ($key !== null) {
            $config = kernel()->getConfig();

            return collection_get($config, $key, $default);
        }

        return kernel()->getConfig();
    }
}

if (!function_exists('parse_body')) {
    function parse_body(MessageInterface $message, array $options = []): mixed
    {
        $contents = trim($message->getBody()->getContents());

        if ($contents) {
            $contentType = $message->getHeader('Content-Type');

            if ($contentType && in_array('application/xml', $contentType) || array_key_exists('type', $options) && $options['type'] === 'xml') {
                $xml = simplexml_load_string($contents);

                if ($xml) {
                    return json_decode(json_encode($xml), true);
                } else if (str_starts_with($contents, '{') && str_ends_with($contents, '}')) {
                    return json_decode($contents, true);
                }
            } else if (array_key_exists('type', $options) && $options['type'] === 'param') {
                $return = [];

                $result = explode(PHP_EOL, $contents);

                foreach ($result as $item) {
                    $value = array_map('trim', explode('=', trim($item)));

                    if ($value[0] != '') {
                        $return[$value[0]] = array_key_exists(1, $value) ? $value[1] : true;
                    }
                }

                return $return;
            } else {
                return json_decode($contents, true);
            }
        }

        return null;
    }
}