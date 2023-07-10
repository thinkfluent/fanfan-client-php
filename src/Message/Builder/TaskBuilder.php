<?php

declare(strict_types=1);

namespace FanFan\Client\Message\Builder;

use FanFan\Client\Message\Task;

class TaskBuilder extends Task
{
    public static function fromPubSub(array $messageData): Task
    {
        $task = new Task();
        foreach (['jobId', 'taskId', 'action'] as $field) {
            if (empty($messageData[$field] ?? '')) {
                throw new \InvalidArgumentException('Missing field: ' . $field);
            }
            $task->{$field} = (string)$messageData[$field];
        }
        if (!empty($messageData['payload'] ?? [])) {
            $task->payload = (array)$messageData['payload'];
        }
        return $task;
    }
}
