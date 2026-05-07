<?php

namespace Snowsoft\LaravelPageSpeed\Middleware;

class OptimizeSeoTags extends PageSpeed
{
    /**
     * Integrate with Artesaos SEOTools to automatically inject and optimize
     * SEO meta tags and Rich Snippets (JSON-LD).
     *
     * @param string $buffer
     * @return string
     */
    public function apply($buffer)
    {
        if (stripos($buffer, '</head>') === false) {
            return $buffer;
        }

        $seoTags = '';

        // 1. Auto-Inject Artesaos SEOTools if it exists and hasn't been printed yet
        if (app()->bound('seotools') && stripos($buffer, 'name="twitter:card"') === false) {
            // Generate all SEO tags from Artesaos (Meta, OpenGraph, Twitter, JsonLd)
            $seoTags = app('seotools')->generate(true);
            
            // Inject them before </head>
            if (!empty($seoTags)) {
                $buffer = preg_replace('/<\/head>/i', "\n" . $seoTags . "\n</head>", $buffer, 1);
            }
        }

        // 2. Extract, Minify and Consolidate all JSON-LD scripts (Rich Snippets)
        $jsonLdScripts = [];
        $buffer = preg_replace_callback(
            '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            function ($matches) use (&$jsonLdScripts) {
                $jsonString = $matches[1];
                $json = json_decode($jsonString, true);
                
                // If valid JSON, collect it and remove it from its current position
                if (json_last_error() === JSON_ERROR_NONE && $json !== null) {
                    $jsonLdScripts[] = $json;
                    return ''; // Remove from current location
                }
                
                return $matches[0];
            },
            $buffer
        );

        // 3. Re-inject all JSON-LDs compactly right before </head>
        if (!empty($jsonLdScripts)) {
            // If there's only one, keep it as object, else array of objects (valid JSON-LD)
            $consolidatedJson = count($jsonLdScripts) === 1 ? $jsonLdScripts[0] : $jsonLdScripts;
            
            $minifiedJson = json_encode($consolidatedJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            $jsonLdHtml = '<script type="application/ld+json">' . $minifiedJson . '</script>';
            $buffer = preg_replace('/<\/head>/i', $jsonLdHtml . "\n</head>", $buffer, 1);
        }

        return $buffer;
    }
}
