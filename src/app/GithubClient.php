<?php

namespace App;


use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use function Swow\Debug\var_dump_return;

class GithubClient
{

    protected Client $httpClient;

    #[Inject]
    protected Config $config;
    
    protected string $baseUrl = 'https://api.github.com/';

    public function __construct()
    {
        $this->httpClient = new Client([
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . $this->getGithubToken(),
            ]
        ]);
    }

    public function getRelease(string $repo, string $version): array
    {
        $baseUrl = $this->buildRepoUrl($repo);
        if ($version == 'latest') {
            $url = $baseUrl . '/releases/' . $version;
        } else {
            $url = $baseUrl . '/releases/tags/' . $version;
        }
        $response = $this->httpClient->get($url);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getActionsArtifacts(string $repo, string $jobId)
    {
        $url = $this->buildRepoUrl($repo) . '/actions/runs/' . $jobId . '/artifacts';
        $response = $this->httpClient->get($url);
        return json_decode($response->getBody()->getContents(), true);
    }

    protected function buildRepoUrl(string $repo): string
    {
        return $this->baseUrl . 'repos/' . $repo;
    }

    public function getGithubToken(): string
    {
        return $this->config->getConfig('github.access-token') ?: '';
    }

}