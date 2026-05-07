<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class AsyncIframes extends PageSpeed
{
    /**
     * Apply lazy loading to iframes
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        // Early return if no iframe tags found
        if (stripos($buffer, '<iframe') === false) {
            return $buffer;
        }

        // Add loading="lazy" to iframes that don't already have a loading attribute
        $result = preg_replace_callback(
            '/<iframe\s+(?![^>]*\bloading=["\'])([^>]*)>/i',
            function ($matches) {
                $attributes = $matches[1];
                
                // If the tag ends with /, place it before the /
                if (substr(trim($attributes), -1) === '/') {
                    $attributes = substr(trim($attributes), 0, -1) . ' loading="lazy" /';
                } else {
                    $attributes .= ' loading="lazy"';
                }
                
                return "<iframe {$attributes}>";
            },
            $buffer
        );

        return $result !== null ? $result : $buffer;
    }
}
