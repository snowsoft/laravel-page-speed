<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class CleanHtmlAttributes extends PageSpeed
{
    /**
     * Remove redundant HTML5 attributes like type="text/javascript" 
     * or empty class attributes to save bytes.
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        $replace = [
            '/\s+type=["\']text\/(javascript|css)["\']/i' => '',
            '/\s+class=["\']\s*["\']/i' => '',
            '/\s+style=["\']\s*["\']/i' => '',
        ];
        
        $result = preg_replace(array_keys($replace), array_values($replace), $buffer);
        
        return $result !== null ? $result : $buffer;
    }
}
