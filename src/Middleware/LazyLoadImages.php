<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class LazyLoadImages extends PageSpeed
{
    /**
     * Apply lazy loading to images
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        // Early return if no img tags found
        if (stripos($buffer, '<img') === false) {
            return $buffer;
        }

        // Performance: Use preg_replace_callback to safely add loading="lazy" 
        // to images that don't already have a loading attribute
        $result = preg_replace_callback(
            '/<img\s+(?![^>]*\bloading=["\'])([^>]*)>/i',
            function ($matches) {
                // Ensure it's not self-closing but we append before the closing >
                $attributes = $matches[1];
                
                // If the tag ends with /, place it before the /
                if (substr(trim($attributes), -1) === '/') {
                    $attributes = substr(trim($attributes), 0, -1) . ' loading="lazy" /';
                } else {
                    $attributes .= ' loading="lazy"';
                }
                
                return "<img {$attributes}>";
            },
            $buffer
        );

        return $result !== null ? $result : $buffer;
    }
}
