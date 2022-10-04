<?php

namespace App\PkgDefinition;

class Jobs
{
    /**
     * @var Job[]
     */
    protected array $jobs;

    public function __construct(array $jobs)
    {
        foreach ($jobs as $arch => $jobId) {
            $this->jobs[$arch] = new Job($arch, $jobId);
        }
    }

    public function getJob(string $arch): ?Job
    {
        $job = $this->jobs[$arch] ?? null;
        return $job instanceof Job ? $job : null;
    }

    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function setJobs(array $jobs): static
    {
        $this->jobs = $jobs;
        return $this;
    }
}
