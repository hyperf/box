<?php

namespace App\PkgDefinition;

class Job
{
    protected string $os;
    protected string $arch;
    protected string $jobId;

    public function __construct(string $osAndArch, string $jobId)
    {
        $this->os = explode('.', $osAndArch)[0] ?? 'Unknown';
        $this->arch = explode('.', $osAndArch)[1] ?? 'x86_64';
        $this->jobId = $jobId;
    }

    public function getOs(): string
    {
        return $this->os;
    }

    public function setOs(string $os): Job
    {
        $this->os = $os;
        return $this;
    }

    public function getArch(): string
    {
        return $this->arch;
    }

    public function setArch(string $arch): Job
    {
        $this->arch = $arch;
        return $this;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function setJobId(string $jobId): Job
    {
        $this->jobId = $jobId;
        return $this;
    }
}
