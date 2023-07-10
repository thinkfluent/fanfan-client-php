<?php

declare(strict_types=1);

namespace FanFan\Client\Message;

use FanFan\Client\TaskStatus;

class TaskOutcome implements \JsonSerializable
{
    use HasPayloadTrait;

    private string $jobId;
    private string $taskId;
    private string $status = TaskStatus::SUCCEEDED;

    public function __construct(string $jobId, string $taskId)
    {
        $this->jobId = $jobId;
        $this->taskId = $taskId;
    }

    public function status(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function formatForPubSub(): array
    {
        return [
            'data' => \json_encode($this->jsonSerialize()),
            'attributes' => [], // for the TaskOutcome
        ];
    }

    public function jsonSerialize(): array {
        $data = [
            'jobId' => $this->jobId,
            'taskId' => $this->taskId,
            'status' => $this->status,
        ];
        if (!empty($this->payload)) {
            $data['payload'] = $this->payload;
        }
        return $data;
    }
}
