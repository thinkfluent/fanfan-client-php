<?php

namespace FanFan\Client\Test\Message;

use FanFan\Client\Message\Builder\TaskBuilder;
use FanFan\Client\Message\TaskOutcome;
use FanFan\Client\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskOutcomeTest extends TestCase
{

    public function testCreateOutcomeFromTask()
    {
        $task = TaskBuilder::fromPubSub([
            'jobId' => '987654',
            'taskId' => '3210',
            'action' => 'create',
        ]);
        $outcome = $task->createOutcome(TaskStatus::SUCCEEDED);
        $this->assertInstanceOf(TaskOutcome::class, $outcome);
        $json = $outcome->jsonSerialize();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('jobId', $json);
        $this->assertArrayHasKey('taskId', $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals('987654', $json['jobId']);
        $this->assertEquals('3210', $json['taskId']);
        $this->assertEquals(TaskStatus::SUCCEEDED, $json['status']);
        $this->assertArrayNotHasKey('payload', $json);
    }

    public function testCreateOutcome()
    {
        $outcome = new TaskOutcome('job123', 'task987');
        $outcome->status(TaskStatus::FAILED);
        $outcome->payload(['b' => 'c']);
        $json = $outcome->jsonSerialize();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('jobId', $json);
        $this->assertArrayHasKey('taskId', $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('payload', $json);
        $this->assertEquals('job123', $json['jobId']);
        $this->assertEquals('task987', $json['taskId']);
        $this->assertEquals(TaskStatus::FAILED, $json['status']);
        $this->assertEquals(['b' => 'c'], $json['payload']);
    }

    public function testFormatForPubSub()
    {
        $outcome = new TaskOutcome('job123', 'task987');
        $pubsub = $outcome->formatForPubSub();
        $this->assertIsArray($pubsub);
        $this->assertArrayHasKey('data', $pubsub);
        $this->assertIsString($pubsub['data']);
    }

}
