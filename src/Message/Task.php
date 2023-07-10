<?php

declare(strict_types=1);

namespace FanFan\Client\Message;

class Task implements \JsonSerializable
{
    use HasPayloadTrait;

    protected string $jobId;
    protected string $taskId;
    protected string $action;

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function jsonSerialize(): array
    {
        $data =  [
            'jobId' => $this->jobId,
            'taskId' => $this->taskId,
            'action' => $this->action,
        ];
        if (!empty($this->payload)) {
            $data['payload'] = $this->payload;
        }
        return $data;
    }

    public function createOutcome(string $status): TaskOutcome
    {
        return (new TaskOutcome($this->jobId, $this->taskId))
            ->status($status);
    }
}
