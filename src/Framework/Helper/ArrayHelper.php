<?php
namespace Framework\Helper;

class ArrayHelper
{
    private static $instance;

    public static function getInstance(): self
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        return self::$instance = new self;
    }

    /**
     * Check if the keys exist in an array.
     * @param array $keys
     * @param array $array
     * @return array|true
     */
    public static function keysExists(array $keys, array $array)
    {
        return array_diff_key($array, $keys) ?: true;
    }

    public static function get(string $index, array $array)
    {
        return self::has($index, $array) ? $array[$index] : null;
    }

    public static function has(string $index, array $array): bool
    {
        return array_key_exists($index, $array) ? true : false;
    }

    public static function first(array $array)
    {
        return array_shift($array);
    }

    public static function next(array $array): array
    {
        array_shift($array);
        return $array;
    }

    public static function find(string $index, array $array)
    {
        if (array_key_exists($index, $array)) {
            return $array[$index];
        }
        $parts = explode('.', $index);
        $firstIndex = self::first($parts);
        $nextIndex = implode('.', self::next($parts));
        if ($firstIndex === '*') {
            return array_column($array, $nextIndex);
        }
        if (is_string(self::get($firstIndex, $array))) {
            return null;
        }
        if (isset($array[$firstIndex])) {
            return self::find($nextIndex, self::get($firstIndex, $array));
        }
        return null;
    }

    public static function groupById(array $array)
    {
        $result = [];
        foreach ($array as $data) {
            $id = $data['id'];
            if (isset($result[$id])) {
                $result[$id][] = $data;
            } else {
                $result[$id] = [$data];
            }
        }
        return $result;
    }

    public static function isSequential(array $array): bool
    {
        if (empty($array)) {
            return true;
        }
        return array_keys($array) === range(0, (count($array) -1));
    }
}
