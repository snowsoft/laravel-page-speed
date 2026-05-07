<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class AutoPreconnect extends PageSpeed
{
    /**
     * Scan external scripts and styles to inject preconnect links in the head.
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        if (stripos($buffer, '<head>') === false) {
            return $buffer;
        }

        preg_match_all('/<script[^>]+src=["\'](https:\/\/[^"\']+)["\']/i', $buffer, $scriptMatches);
        preg_match_all('/<link[^>]+href=["\'](https:\/\/[^"\']+)["\']/i', $buffer, $linkMatches);
        
        $urls = array_merge($scriptMatches[1] ?? [], $linkMatches[1] ?? []);
        
        if (empty($urls)) {
            return $buffer;
        }
        
        $domains = [];
        $requestHost = request()->getHost();
        
        foreach ($urls as $url) {
            $parsed = parse_url($url);
            if (isset($parsed['host']) && $parsed['host'] !== $requestHost) {
                $domains[] = 'https://' . $parsed['host'];
            }
        }
        
        $domains = array_unique($domains);
        
        if (empty($domains)) {
            return $buffer;
        }
        
        $preconnectTags = '';
        foreach ($domains as $domain) {
            $preconnectTags .= '<link rel="preconnect" href="'.$domain.'" crossorigin>' . "\n";
        }
        
        $result = preg_replace('/<head>/i', "<head>\n" . $preconnectTags, $buffer, 1);
        
        return $result !== null ? $result : $buffer;
    }
}
