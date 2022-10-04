<?php

namespace App\PkgDefinition;

class Definition
{
    protected string $pkgName = '';
    protected ?string $repo = null;
    protected ?string $bin = null;
    protected ?string $latest = null;
    protected ?string $latestFetchType = null;
    protected ?string $url = null;
    protected ?Jobs $jobs = null;
    protected array $jobArtifactMatchRule = [];
    protected array $releaseAssetMatchRule = [];
    protected string $releaseAssetKeyword = '';
    protected array $versions = [];
    protected ?Sources $sources = null;

    public function __construct(string $pkgName, array $data = [])
    {
        $this->pkgName = $pkgName;
        isset($data['repo']) && is_string($data['repo']) && $this->setRepo($data['repo']);
        isset($data['bin']) && is_string($data['bin']) && $this->setBin($data['bin']);
        isset($data['latest']) && is_string($data['latest']) && $this->setLatest($data['latest']);
        isset($data['latest_fetch_type']) && is_string($data['latest_fetch_type']) && $this->setLatestFetchType($data['latest_fetch_type']);
        isset($data['url']) && is_string($data['url']) && $this->setUrl($data['url']);
        isset($data['jobs']) && is_array($data['jobs']) && $this->setJobs(new Jobs($data['jobs']));
        isset($data['job_artifact_match_rule']) && is_array($data['job_artifact_match_rule']) && $this->setJobArtifactMatchRule($data['job_artifact_match_rule']);
        isset($data['release_asset_match_rule']) && is_array($data['release_asset_match_rule']) && $this->setReleaseAssetMatchRule($data['release_asset_match_rule']);
        isset($data['release_asset_keyword']) && is_string($data['release_asset_keyword']) && $this->setReleaseAssetKeyword($data['release_asset_keyword']);
        isset($data['versions']) && is_array($data['versions']) && $this->setVersions($data['versions']);
        isset($data['sources']) && is_array($data['sources']) && $this->setSources(new Sources($data['sources']));
    }

    public function getRepo(): ?string
    {
        return $this->repo;
    }

    public function setRepo(?string $repo): Definition
    {
        $this->repo = $repo;
        return $this;
    }

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(?string $bin): Definition
    {
        $this->bin = $bin;
        return $this;
    }

    public function getLatest(): ?string
    {
        return $this->latest;
    }

    public function setLatest(?string $latest): Definition
    {
        $this->latest = $latest;
        return $this;
    }

    public function getLatestFetchType(): ?string
    {
        return $this->latestFetchType;
    }

    public function setLatestFetchType(?string $latestFetchType): Definition
    {
        $this->latestFetchType = $latestFetchType;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): Definition
    {
        $this->url = $url;
        return $this;
    }

    public function getJobs(): ?Jobs
    {
        return $this->jobs;
    }

    public function setJobs(?Jobs $jobs): Definition
    {
        $this->jobs = $jobs;
        return $this;
    }

    public function getJobArtifactMatchRule(): array
    {
        return $this->jobArtifactMatchRule;
    }

    public function setJobArtifactMatchRule(array $jobArtifactMatchRule): Definition
    {
        $this->jobArtifactMatchRule = $jobArtifactMatchRule;
        return $this;
    }

    public function getReleaseAssetMatchRule(): array
    {
        return $this->releaseAssetMatchRule;
    }

    public function setReleaseAssetMatchRule(array $releaseAssetMatchRule): Definition
    {
        $this->releaseAssetMatchRule = $releaseAssetMatchRule;
        return $this;
    }

    public function getVersions(): array
    {
        return $this->versions;
    }

    public function setVersions(array $versions): Definition
    {
        $this->versions = $versions;
        return $this;
    }

    public function getSources(): Sources
    {
        return $this->sources;
    }

    public function setSources(Sources $sources): Definition
    {
        $this->sources = $sources;
        return $this;
    }

    public function getReleaseAssetKeyword(): string
    {
        return $this->releaseAssetKeyword;
    }

    public function setReleaseAssetKeyword(string $releaseAssetKeyword): Definition
    {
        $this->releaseAssetKeyword = $releaseAssetKeyword;
        return $this;
    }
}
