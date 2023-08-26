<?php

declare(strict_types=1);

namespace FanFan\Client\Message\Builder;

use FanFan\Client\Message\JobRequest;

class JobRequestBuilder
{
    public static function fromPubSub(array $requestData): JobRequest
    {
        foreach (['action', 'fanout'] as $field) {
            if (empty($requestData[$field] ?? '')) {
                throw new \InvalidArgumentException('Missing field: ' . $field);
            }
        }
        $request = new JobRequest();
        $request->action((string)$requestData['action']);
        $fanout = (array) $requestData['fanout'];
        if (!empty($fanout['tasks'] ?? 0)) {
            $request->fixedTaskCount((int)$fanout['tasks']);
        } else if (!empty($fanout['range'] ?? [])) {
            // @todo validate subfields
            $request->range(
                (string)$fanout['range']['as'],
                (int)$fanout['range']['start'],
                (int)$fanout['range']['stop'],
                (int)$fanout['range']['step'],
            );
        } else if (!empty($fanout['foreach'] ?? [])) {
            // @todo validate subfields
            $request->forEach(
                (string)$fanout['foreach']['as'],
                (array)$fanout['foreach']['items']
            );
        } else {
            throw new \InvalidArgumentException('Invalid fanout spec');
        }
        if (!empty($requestData['payload'] ?? [])) {
            $request->payload((array)$requestData['payload']);
        }
        if (!empty($requestData['taskTopic'] ?? '')) {
            $request->taskTopic((string)$requestData['taskTopic']);
        }
        if (!empty($requestData['doneTopic'] ?? '')) {
            $request->doneTopic((string)$requestData['doneTopic']);
        }
        if (!empty($requestData['taskAttributes'] ?? [])) {
            $request->taskAttributes((array)$requestData['taskAttributes']);
        }
        return $request;
    }
}
