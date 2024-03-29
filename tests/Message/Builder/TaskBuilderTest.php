<?php

namespace FanFan\Client\Test\Message\Builder;

use FanFan\Client\Message\Builder\TaskBuilder;
use PHPUnit\Framework\TestCase;

class TaskBuilderTest extends TestCase
{

    public function testFailMissingRequiredFields()
    {
        $baseConfig = [
            'jobId' => '1234',
            'taskId' => '1235',
            'action' => 'delete',
        ];
        foreach (array_keys($baseConfig) as $requiredField) {
            $thisConfig = $baseConfig;
            unset($thisConfig[$requiredField]);
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Missing field: ' . $requiredField);
            TaskBuilder::fromPubSub($thisConfig);
        }
    }

    public function testFieldsAndOptionalPayload()
    {
        $task = TaskBuilder::fromPubSub([
            'jobId' => '1234',
            'taskId' => '1235',
            'action' => 'delete',
            'payload' => [
                'some' => 'data',
            ],
        ]);
        $this->assertEquals('1234', $task->getJobId());
        $this->assertEquals('1235', $task->getTaskId());
        $this->assertEquals('delete', $task->getAction());
        $this->assertEquals(['some' => 'data'], $task->getPayload());
    }

}
