<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Symfony\Component\HttpFoundation\HeaderBag;

class LoggingRedactor
{
    /**
     * Get the array of redaction keys.
     * @return array
     */
    public static function getKeys(): array
    {
        return config('laraveleslogs.redaction');
    }

    /**
     * Get the ink for the redaction.
     * @return string
     */
    public static function getInk(): string
    {
        return config('laraveleslogs.redaction_ink');
    }

    /**
     * Replace keys in an array with redactor $ink.
     * @param $content
     * @return array
     */
    public static function redactArray($content)
    {
        if (! is_array($content)) {
            return $content;
        }

        // Recursively traverse the array and redact the specified keys
        array_walk_recursive($content, function (&$value, $key) {
            if (in_array($key, self::getKeys(), true)) {
                $value = self::partialReplace($value);
            }
        });

        return $content;
    }

    /**
     * Redact keys from within a HeaderBag object.
     * @param HeaderBag $headerBag
     * @return string
     */
    public static function redactHeaderBag(HeaderBag $headerBag): string
    {
        $headers = '';
        foreach ($headerBag->keys() as $key) {
            $header = $key;
            $value = $headerBag->get($key);

            if (in_array($key, self::getKeys())) {
                $value = self::partialReplace($headerBag->get($key));
            }
            $headers .= $header.' : '.$value.PHP_EOL;
        }

        return $headers;
    }

    /**
     * Replace keys in json data with redactor $ink.
     * @param $content
     * @return string
     */
    public static function redactJson($content): string
    {
        $json = json_decode($content, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $content;
        }

        $redacted = self::redactArray($json);

        return json_encode($redacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Partially replace string with replacement in the middle.
     * @param string $input
     * @return string|null
     */
    public static function partialReplace(?string $input): ?string
    {
        if (is_null($input)) {
            return null;
        }
        $length = strlen($input);
        if ($length == 0) {
            return $input;
        }

        $first_two_chars = substr($input, 0, 2);
        $last_two_chars = substr($input, -2);

        return $first_two_chars.self::getInk().$last_two_chars;
    }
}
