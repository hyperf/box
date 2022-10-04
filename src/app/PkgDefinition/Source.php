<?php

namespace App\PkgDefinition;

class Source
{
    protected string $name;
    protected string $type = 'github';
    protected string $url = '';

    public function __construct(string $name, array $data)
    {
        $this->setName($name);
        isset($data['type']) && is_string($data['type']) && $this->setType($data['type']);
        isset($data['url']) && is_string($data['url']) && $this->setUrl($data['url']);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Source
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Source
    {
        $this->type = $type;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Source
    {
        $this->url = $url;
        return $this;
    }
}
