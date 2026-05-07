<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class AutoPrefetch extends PageSpeed
{
    /**
     * Inject instant.page script for auto-prefetching links on hover.
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        if (stripos($buffer, '</body>') === false) {
            return $buffer;
        }

        // Add instant.page script just before closing body tag
        $script = '<script src="https://unpkg.com/instant.page@5.2.0/instantpage.js" type="module" defer></script>';
        
        $result = preg_replace('/<\/body>/i', $script . "\n" . '</body>', $buffer, 1);
        
        return $result !== null ? $result : $buffer;
    }
}
