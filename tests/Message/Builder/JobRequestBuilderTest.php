<?php

namespace FanFan\Client\Test\Message\Builder;

use FanFan\Client\Message\Builder\JobRequestBuilder;
use FanFan\Client\Message\JobRequest;
use PHPUnit\Framework\TestCase;

class JobRequestBuilderTest extends TestCase
{

    public function testFailMissingRequiredFields()
    {
        $baseConfig = [
            'action' => 'delete',
            'fanout' => [
                'tasks' => 31,
            ],
        ];
        foreach (array_keys($baseConfig) as $requiredField) {
            $thisConfig = $baseConfig;
            unset($thisConfig[$requiredField]);
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Missing field: ' . $requiredField);
            JobRequestBuilder::fromPubSub($thisConfig);
        }
    }

    public function testBasicFields()
    {
        $request = JobRequestBuilder::fromPubSub([
            'action' => 'create',
            'fanout' => [
                'tasks' => 24,
            ],
        ]);
        $this->assertEquals(JobRequest::FANOUT_TYPE_FIXED, $request->getFanoutType());
        $this->assertEquals(['tasks' => 24], $request->getFanoutConfig());
        $this->assertEquals('create', $request->getAction());
        // Missing fields
        $this->assertNull($request->getTaskTopic());
        $this->assertNull($request->getDoneTopic());
        $this->assertEquals([], $request->getTaskAttributes());
        $this->assertNull($request->getPayload());
    }

    public function testFullFields()
    {
        $request = JobRequestBuilder::fromPubSub([
            'action' => 'update',
            'fanout' => [
                'range' => [
                    'as' => 'age',
                    'start' => 21,
                    'stop' => 25,
                    'step' => 1,
                ],
            ],
            'payload' => [
                'name' => 'McFly',
            ],
            'taskTopic' => 'some/task/topic',
            'doneTopic' => 'some/done/topic',
            'taskAttributes' => ['x' => 'y'],
        ]);
        $this->assertEquals(JobRequest::FANOUT_TYPE_RANGE, $request->getFanoutType());
        $this->assertEquals(
            ['range' => [
                'as' => 'age',
                'start' => 21,
                'stop' => 25,
                'step' => 1,
            ]],
            $request->getFanoutConfig()
        );
        $this->assertEquals('update', $request->getAction());
        $this->assertEquals('some/task/topic', $request->getTaskTopic());
        $this->assertEquals('some/done/topic', $request->getDoneTopic());
        $this->assertEquals(['x' => 'y'], $request->getTaskAttributes());
        $this->assertEquals(['name' => 'McFly'], $request->getPayload());
    }

    public function testForeachBuild()
    {
        $request = JobRequestBuilder::fromPubSub([
            'action' => 'recreate',
            'fanout' => [
                'foreach' => [
                    'as' => 'destinations',
                    'items' => [1985, 2015],
                ],
            ],
        ]);
        $this->assertEquals(JobRequest::FANOUT_TYPE_FOREACH, $request->getFanoutType());
        $this->assertEquals(
            ['foreach' => [
                'as' => 'destinations',
                'items' => [1985, 2015],
            ]],
            $request->getFanoutConfig()
        );
    }

}