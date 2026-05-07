<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class DeferScripts extends PageSpeed
{
    /**
     * Automatically add defer attribute to script tags that don't have it.
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        if (stripos($buffer, '<script') === false) {
            return $buffer;
        }

        $result = preg_replace_callback(
            '/<script\s+([^>]*\bsrc=["\'][^"\']+["\'][^>]*)>/i',
            function ($matches) {
                $attributes = $matches[1];
                
                // Skip if already has async, defer, data-no-defer or module
                if (
                    preg_match('/\b(async|defer|data-no-defer)\b/i', $attributes) || 
                    preg_match('/\btype=["\']module["\']/i', $attributes)
                ) {
                    return $matches[0];
                }
                
                // If the tag ends with /, place it before the /
                if (substr(trim($attributes), -1) === '/') {
                    $attributes = substr(trim($attributes), 0, -1) . ' defer /';
                } else {
                    $attributes .= ' defer';
                }
                
                return "<script {$attributes}>";
            },
            $buffer
        );

        return $result !== null ? $result : $buffer;
    }
}
