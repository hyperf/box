<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App;

use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Str;

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
            ],
            'verify' => false
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

    public function getReleases(string $fullRepo): array
    {
        $url = $this->buildRepoUrl($fullRepo) . '/releases';
        $response = $this->httpClient->get($url);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getActionsArtifacts(string $repo, string $jobId, ?int $page = null)
    {
        $url = $this->buildRepoUrl($repo) . '/actions/runs/' . $jobId . '/artifacts';
        if ($page) {
            $url .= '?page=' . $page;
        }
        $response = $this->httpClient->get($url);
        $artifacts = json_decode($response->getBody()->getContents(), true);
        if ($response->hasHeader('link')) {
            $links = [];
            $link = $response->getHeaderLine('link');
            $explodedLinks = explode(', ', $link);
            foreach ($explodedLinks as $explodedLink) {
                [$link, $rel] = explode(';', $explodedLink);
                $link = trim($link, '<>');
                $pageNum = Str::afterLast($link, '=');
                $rel = str_replace('rel=', '', trim($rel));
                $rel = str_replace('"', '', $rel);
                $links[$rel] = ['link' => $link, 'page' => $pageNum];
            }
            if (isset($links['next']['page']) && is_numeric($links['next']['page'])) {
                $nextPageArtifacts = $this->getActionsArtifacts($repo, $jobId, intval($links['next']['page']));
                $artifacts['artifacts'] = array_merge($artifacts['artifacts'], $nextPageArtifacts['artifacts']);
            }
        }
        return $artifacts;
    }

    public function getGithubToken(): string
    {
        return $this->config->getConfig('github.access-token') ?: '';
    }

    protected function buildRepoUrl(string $repo): string
    {
        return $this->baseUrl . 'repos/' . $repo;
    }
}
