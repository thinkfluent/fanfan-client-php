<?php

declare(strict_types=1);

namespace FanFan\Client\Message\Builder;

use FanFan\Client\Message\JobOutcome;

class JobOutcomeBuilder extends JobOutcome
{
    public static function fromPubSub(array $messageData): JobOutcome
    {
        $outcome = new JobOutcome();
        foreach (['jobId', 'status', 'startedTsp', 'taskCounts', 'request'] as $field) {
            if (empty($messageData[$field] ?? '')) {
                throw new \InvalidArgumentException('Missing field: ' . $field);
            }
        }
        $outcome->jobId = (string)$messageData['jobId'];
        $outcome->status = (string)$messageData['status'];
        $outcome->startedTsp = (int)$messageData['startedTsp'];
        $outcome->tookMs = (int)($messageData['tookMs'] ?? 0);
        $outcome->taskCounts = (array)$messageData['taskCounts'];
        $outcome->request = JobRequestBuilder::fromPubSub((array)$messageData['request']);
        if (!empty($messageData['payload'] ?? [])) {
            $outcome->payload = (array)$messageData['payload'];
        }
        return $outcome;
    }
}
