<?php

namespace App\PkgDefinition;

class Sources
{
    protected array $sources = [];

    public function __construct(array $sources)
    {
        foreach ($sources as $name => $data) {
            $this->sources[$name] = new Source($name, $data);
        }
    }

    public function getSource(string $name): ?Source
    {
        $source = $this->sources[$name] ?? null;
        return $source instanceof Source ? $source : null;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function setSources(array $sources): Sources
    {
        // Validate the value is an array of Source objects
        foreach ($sources as $source) {
            if (!($source instanceof Source)) {
                throw new \InvalidArgumentException('Invalid value for sources');
            }
        }
        $this->sources = $sources;
        return $this;
    }
}
