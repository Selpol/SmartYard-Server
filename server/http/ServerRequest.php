<?php

namespace Selpol\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Selpol\Http\Trait\MessageTrait;
use Selpol\Http\Trait\RequestTrait;

class ServerRequest implements ServerRequestInterface
{
    use MessageTrait;
    use RequestTrait;

    private array $attributes = [];
    private mixed $parsedBody = null;

    private array $cookiesParams = [];
    private array $queryParams = [];
    private array $serverParams;

    /** @var UploadedFileInterface[] $uploadedFiles */
    private array $uploadedFiles = [];

    public function __construct(string $method, string|UriInterface $uri, array $headers = [], ?StreamInterface $body = null, string $version = '1.1', array $serverParams = [])
    {
        $this->method = $method;
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->headers = $headers;

        if ($body !== null)
            $this->body = $body;

        $this->protocolVersion = $version;

        $this->serverParams = $serverParams;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    public function getCookieParams(): array
    {
        return $this->cookiesParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $this->cookiesParams = $cookies;

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $this->queryParams = $query;

        return $this;
    }

    public function getParsedBody()
    {
        if ($this->parsedBody === null && isset($this->body))
            $this->parsedBody = json_decode($this->body->getContents(), true);

        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $this->parsedBody = $data;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (array_key_exists($name, $this->attributes))
            unset($this->attributes[$name]);

        return $this;
    }
}