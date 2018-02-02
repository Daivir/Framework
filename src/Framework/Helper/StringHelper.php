<?php
namespace Framework\Helper;

/**
 * Class StringHelper
 * @package Framework\Helper
 */
class StringHelper
{
    /**
     * Camelize a string
     *
     * @param string $string
     * @return string
     */
    public static function camelize(string $string): string
    {
        return lcfirst(
            join('', array_map('ucfirst', explode('_', $string)))
        );
    }
}
