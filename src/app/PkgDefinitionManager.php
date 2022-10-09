<?php

namespace App;

use App\PkgDefinition\Definition;
use GuzzleHttp\Client;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

class PkgDefinitionManager
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    // Use 'file://pkgs.json' to load local file for development
    protected string $url = 'https://raw.githubusercontent.com/hyperf/box/master/pkgs.json';

    /**
     * All the definitions is array data, not Definition instance.
     */
    protected ?array $pkgs = null;

    public function getDefinition(string $pkg): ?Definition
    {
        foreach ($this->getPkgs() as $name => $item) {
            if ($name === $pkg) {
                return new Definition($name, $item);
            }
        }
        return null;
    }

    public function hasDefinition(string $pkg): bool
    {
        return isset($this->getPkgs()[$pkg]);
    }

    public function getPkgs(): array
    {
        if (is_null($this->pkgs)) {
            $this->fetchPkgs();
        }
        return $this->pkgs;
    }

    public function fetchPkgs(): bool
    {
        try {
            // Is url start with file:// ?
            if (str_starts_with($this->url, 'file://')) {
                $path = substr($this->url, 7);
                if (! file_exists($path)) {
                    $this->logger->error(sprintf('File %s not exists.', $path));
                    return false;
                }
                $this->pkgs = json_decode(file_get_contents($path), true);
            } else {
                $response = (new Client(['verify' => false]))->get($this->url);
                if ($response->getStatusCode() === 200) {
                    $this->pkgs = json_decode($response->getBody()->getContents(), true);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }
        return true;
    }
}
