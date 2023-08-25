<?php

namespace FanFan\Client\Test\Message\Builder;

use FanFan\Client\JobStatus;
use FanFan\Client\Message\Builder\JobOutcomeBuilder;
use FanFan\Client\Message\JobOutcome;
use FanFan\Client\Message\JobRequest;
use PHPUnit\Framework\TestCase;

class JobOutcomeBuilderTest extends TestCase
{

    public function testBasicJobOutcome()
    {
        $outcome = JobOutcomeBuilder::fromPubSub([
            'jobId' => '123',
            'status' => JobStatus::SUCCEEDED,
            'startedTsp' => 123456789,
            'taskCounts' => [
                JobStatus::SUCCEEDED => 1,
            ],
            'request' => (new JobRequest())->fixedTaskCount(42)->jsonSerialize(),
        ]);
        $this->assertEquals('123', $outcome->getJobId());
        $this->assertEquals(JobStatus::SUCCEEDED, $outcome->getStatus());
        $this->assertEquals(123456789, $outcome->getStartedTimestamp());
        $this->assertEquals([JobStatus::SUCCEEDED => 1], $outcome->getTaskCounts());
        $this->assertInstanceOf(JobRequest::class, $outcome->getJobRequest());
        $request = $outcome->getJobRequest();
        $this->assertEquals(JobRequest::FANOUT_TYPE_FIXED, $request->getFanoutType());
    }

    public static function getCompleteJobOutcome(): JobOutcome
    {
        return JobOutcomeBuilder::fromPubSub([
            'jobId' => '12345',
            'status' => JobStatus::FAILED,
            'startedTsp' => 12345678910,
            'tookMs' => 101,
            'taskCounts' => [
                JobStatus::SUCCEEDED => 0,
                JobStatus::FAILED => 10,
            ],
            'request' => (new JobRequest())->forEach('test', [1, 2, 3])->jsonSerialize(),
            'payload' => [
                'some' => 'data',
            ],
        ]);
    }

    public function testCompleteJobOutcome()
    {
        $outcome = self::getCompleteJobOutcome();
        $this->assertEquals('12345', $outcome->getJobId());
        $this->assertEquals(JobStatus::FAILED, $outcome->getStatus());
        $this->assertEquals(12345678910, $outcome->getStartedTimestamp());
        $this->assertEquals(101, $outcome->getTookMs());
        $this->assertEquals(
            [
                JobStatus::SUCCEEDED => 0,
                JobStatus::FAILED => 10,
            ],
            $outcome->getTaskCounts()
        );
        $this->assertInstanceOf(JobRequest::class, $outcome->getJobRequest());
        $request = $outcome->getJobRequest();
        $this->assertEquals(JobRequest::FANOUT_TYPE_FOREACH, $request->getFanoutType());
        $this->assertEquals(['some' => 'data'], $outcome->getPayload());
    }

    public function testFailMissingRequiredFields()
    {
        $baseConfig = [
            'jobId' => '123',
            'status' => JobStatus::SUCCEEDED,
            'startedTsp' => 123456789,
            'taskCounts' => [
                JobStatus::SUCCEEDED => 1,
            ],
            'request' => (new JobRequest())->fixedTaskCount(42)->jsonSerialize(),
        ];
        foreach (array_keys($baseConfig) as $requiredField) {
            $thisConfig = $baseConfig;
            unset($thisConfig[$requiredField]);
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Missing field: ' . $requiredField);
            JobOutcomeBuilder::fromPubSub($thisConfig);
        }
    }
}