<?php

namespace FanFan\Client\Test\Message;

use FanFan\Client\JobStatus;
use FanFan\Client\Message\Builder\JobOutcomeBuilder;
use FanFan\Client\Message\JobOutcome;

use FanFan\Client\Message\JobRequest;
use FanFan\Client\Test\Message\Builder\JobOutcomeBuilderTest;
use PHPUnit\Framework\TestCase;

class JobOutcomeTest extends TestCase
{

    private function buildBasicJobOutcome(): JobOutcome
    {
        return JobOutcomeBuilder::fromPubSub([
            'jobId' => '123',
            'status' => JobStatus::SUCCEEDED,
            'startedTsp' => 123456789,
            'taskCounts' => [
                JobStatus::SUCCEEDED => 1,
            ],
            'request' => (new JobRequest())->fixedTaskCount(1)->jsonSerialize(),
        ]);
    }

    public function testSerialize()
    {
        $outcome = JobOutcomeBuilderTest::getCompleteJobOutcome();
        $json = $outcome->jsonSerialize();
        $this->assertArrayHasKey('jobId', $json);
        $this->assertEquals('12345', $json['jobId']);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals(JobStatus::FAILED, $json['status']);
        $this->assertArrayHasKey('taskCounts', $json);
        $this->assertEquals(
            [
                JobStatus::SUCCEEDED => 0,
                JobStatus::FAILED => 10,
            ],
            $json['taskCounts']
        );
        $this->assertArrayHasKey('startedTsp', $json);
        $this->assertEquals(12345678910, $json['startedTsp']);
        $this->assertArrayHasKey('tookMs', $json);
        $this->assertEquals(101, $json['tookMs']);
        $this->assertArrayHasKey('request', $json);
        $this->assertIsArray($json['request']);
        $this->assertArrayHasKey('payload', $json);
        $this->assertEquals(['some' => 'data'], $json['payload']);
    }

    public function testPayload()
    {
        $message = $this->buildBasicJobOutcome();
        $this->assertFalse($message->hasPayload());
        $before = $message->jsonSerialize();
        $this->assertArrayNotHasKey('payload', $before);
        $fluent = $message->payload(['x' => 'y']);
        $this->assertSame($message, $fluent);
        $this->assertTrue($message->hasPayload());
        $this->assertEquals(['x' => 'y'], $message->getPayload());
        $json = $message->jsonSerialize();
        $this->assertArrayHasKey('payload', $json);
        $this->assertEquals(['x' => 'y'], $json['payload']);
    }
}