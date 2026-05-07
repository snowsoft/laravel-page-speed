<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class CollapseWhitespace extends PageSpeed
{
    /**
     * Tags where whitespace should be preserved
     * 
     * Note: <script> and <style> are NOT included because:
     * - JavaScript and CSS minification is handled by their specific optimizers
     * - Collapsing whitespace in JS/CSS is generally safe and desired
     * - This middleware focuses on preserving user-visible formatted content
     */
    protected const PRESERVE_TAGS = [
        'pre',
        'code',
        'textarea',
    ];

    /**
     * Apply whitespace collapse to buffer while preserving content in specific tags
     */
    public function apply($buffer)
    {
        // Early return if no HTML tags found
        if (stripos($buffer, '<html') === false && stripos($buffer, '<!DOCTYPE') === false) {
            return $buffer;
        }

        // First remove comments
        $buffer = $this->removeComments($buffer);

        // Extract and preserve content from whitespace-sensitive tags
        $preserved = [];
        $buffer = $this->extractPreservedContent($buffer, $preserved);

        // Apply whitespace collapse to the remaining content
        $replace = [
            "/\n([\S])/" => '$1',
            "/\r/" => '',
            "/\n/" => '',
            "/\t/" => '',
            "/ +/" => ' ',
            // Keep one space between tags for Livewire/Alpine.js compatibility (Issue #165)
            // This prevents breaking wire:* directives and x-* attributes
            "/> +</" => '> <',
        ];

        $buffer = $this->replace($replace, $buffer);

        // Restore preserved content
        $buffer = $this->restorePreservedContent($buffer, $preserved);

        return $buffer;
    }

    /**
     * Extract content from tags that should preserve whitespace
     */
    protected function extractPreservedContent(string $buffer, array &$preserved): string
    {
        $index = 0;

        $preserveCallback = function (array $matches) use (&$preserved, &$index): string {
            $placeholder = "___PRESERVED_CONTENT_{$index}___";
            $preserved[$placeholder] = $matches[0];
            $index++;

            return $placeholder;
        };

        foreach (self::PRESERVE_TAGS as $tag) {
            $pattern = '/<('.$tag.')(\s[^>]*)?>(.*?)<\/\1>/is';
            $result = preg_replace_callback($pattern, $preserveCallback, $buffer);
            if ($result !== null) {
                $buffer = $result;
            }
        }

        $attrPattern = '/\b(wire:snapshot|wire:effects|x-data|x-init)\s*=\s*(["\'])(.*?)\2/is';
        $result = preg_replace_callback($attrPattern, $preserveCallback, $buffer);
        if ($result !== null) {
            $buffer = $result;
        }

        return $buffer;
    }

    /**
     * Restore preserved content back into the buffer
     */
    protected function restorePreservedContent(string $buffer, array $preserved): string
    {
        if (empty($preserved)) {
            return $buffer;
        }

        return strtr($buffer, $preserved);
    }

    /**
     * Remove comments before collapsing whitespace
     */
    protected function removeComments($buffer)
    {
        return (new RemoveComments)->apply($buffer);
    }
}
