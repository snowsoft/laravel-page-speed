<?php

namespace Snowsoft\LaravelPageSpeed\Test\Middleware;

use Snowsoft\LaravelPageSpeed\Middleware\TrimUrls;
use Snowsoft\LaravelPageSpeed\Test\TestCase;

class TrimUrlsTest extends TestCase
{
    protected function getMiddleware()
    {
        $this->middleware = new TrimUrls();
    }

    public function test_trim_urls(): void
    {
        $response = $this->middleware->handle($this->request, $this->getNext());

        $this->assertStringNotContainsString("https://", $response->getContent());
        $this->assertStringNotContainsString("http://", $response->getContent());
        $this->assertStringContainsString("//code.jquery.com/jquery-3.2.1.min.js", $response->getContent());
    }
}
