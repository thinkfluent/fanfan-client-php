<?php

declare(strict_types=1);

namespace FanFan\Client\Message;

use FanFan\Client\JobStatus;

class JobOutcome implements \JsonSerializable
{
    use HasPayloadTrait;

    protected string $jobId;
    protected string $status = JobStatus::SUCCEEDED;
    protected int $startedTsp;
    protected int $tookMs;
    protected array $taskCounts;
    protected JobRequest $request;

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartedTimestamp(): int
    {
        return $this->startedTsp;
    }

    public function getTookMs(): int
    {
        return $this->tookMs;
    }

    public function getTaskCounts(): array
    {
        return $this->taskCounts;
    }

    public function getJobRequest(): JobRequest
    {
        return $this->request;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'jobId' => $this->jobId,
            'status' => $this->status,
            'taskCounts' => $this->taskCounts,
            'startedTsp' => $this->startedTsp,
            'tookMs' => $this->tookMs,
            'request' => $this->request->jsonSerialize(),
        ];
        if (!empty($this->payload)) {
            $data['payload'] = $this->payload;
        }
        return $data;
    }
}
