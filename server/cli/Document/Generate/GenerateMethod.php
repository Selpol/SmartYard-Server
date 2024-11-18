<?php declare(strict_types=1);

namespace Selpol\Cli\Document\Generate;

readonly class GenerateMethod
{
    public string $name;

    public string $method;

    public string $path;

    /**
     * @var GenerateParameter[]
     */
    public array $parameters;

    public GenerateDocument|bool $document;

    public function __construct(string $name, string $method, string $path, array $parameters, GenerateDocument|bool $document)
    {
        $this->name = $name;

        $this->method = $method;

        $this->path = $path;

        $this->parameters = $parameters;

        $this->document = $document;
    }
}