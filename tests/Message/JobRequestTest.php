<?php

namespace FanFan\Client\Test\Message;

use FanFan\Client\Message\JobRequest;
use PHPUnit\Framework\TestCase;

class JobRequestTest extends TestCase
{

    public function testNoFanout()
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = new JobRequest();
        $message->jsonSerialize();
    }

    public function testFixedTaskCount()
    {
        $message = new JobRequest();
        $fluent = $message->fixedTaskCount(11);
        $this->assertSame($message, $fluent);
        $json = $message->jsonSerialize();
        $this->assertArrayHasKey('fanout', $json);
        $this->assertArrayHasKey('tasks', $json['fanout']);
        $this->assertEquals(11, $json['fanout']['tasks']);
        $this->assertEquals(JobRequest::FANOUT_TYPE_FIXED, $message->getFanoutType());
    }

    public function testForEach()
    {
        $message = new JobRequest();
        $fluent = $message->forEach('test', ['a', 'b', 'c']);
        $this->assertSame($message, $fluent);
        $json = $message->jsonSerialize();
        $this->assertArrayHasKey('fanout', $json);
        $this->assertArrayHasKey('foreach', $json['fanout']);
        $this->assertEquals(
            ['as' => 'test', 'items' => ['a', 'b', 'c']],
            $json['fanout']['foreach']
        );
        $this->assertEquals(JobRequest::FANOUT_TYPE_FOREACH, $message->getFanoutType());
    }

    public function testRange()
    {
        $message = new JobRequest();
        $fluent = $message->range('years', 1979, 1982);
        $this->assertSame($message, $fluent);
        $json = $message->jsonSerialize();
        $this->assertArrayHasKey('fanout', $json);
        $this->assertArrayHasKey('range', $json['fanout']);
        $this->assertEquals(
            [
                'as' => 'years',
                'start' => 1979,
                'stop' => 1982,
                'step' => 1,
            ],
            $json['fanout']['range']
        );
        $this->assertEquals(JobRequest::FANOUT_TYPE_RANGE, $message->getFanoutType());
    }

    public function testAction()
    {
        $message = new JobRequest();
        $fluent = $message->action('stations');
        $this->assertSame($message, $fluent);
        $message->fixedTaskCount(1);
        $json = $message->jsonSerialize();
        $this->assertArrayHasKey('action', $json);
        $this->assertEquals('stations', $json['action']);
        $this->assertEquals('stations', $message->getAction());
    }

    public function testTaskTopic()
    {
        $message = new JobRequest();
        $message->fixedTaskCount(1);
        $before = $message->jsonSerialize();
        $this->assertArrayNotHasKey('taskTopic', $before);
        $fluent = $message->taskTopic('testing/one/three');
        $this->assertSame($message, $fluent);
        $after = $message->jsonSerialize();
        $this->assertArrayHasKey('taskTopic', $after);
        $this->assertEquals('testing/one/three', $after['taskTopic']);
        $this->assertEquals('testing/one/three', $message->getTaskTopic());
    }

    public function testDoneTopic()
    {
        $message = new JobRequest();
        $message->fixedTaskCount(1);
        $before = $message->jsonSerialize();
        $this->assertArrayNotHasKey('doneTopic', $before);
        $fluent = $message->doneTopic('testing/one/two');
        $this->assertSame($message, $fluent);
        $after = $message->jsonSerialize();
        $this->assertArrayHasKey('doneTopic', $after);
        $this->assertEquals('testing/one/two', $after['doneTopic']);
        $this->assertEquals('testing/one/two', $message->getDoneTopic());
    }

    public function testTaskAttributes()
    {
        $message = new JobRequest();
        $message->fixedTaskCount(1);
        $before = $message->jsonSerialize();
        $this->assertArrayNotHasKey('taskAttributes', $before);
        $fluent = $message->taskAttributes(['a' => 'b']);
        $this->assertSame($message, $fluent);
        $after = $message->jsonSerialize();
        $this->assertArrayHasKey('taskAttributes', $after);
        $this->assertEquals(['a' => 'b'], $after['taskAttributes']);
        $this->assertEquals(['a' => 'b'], $message->getTaskAttributes());
    }

    public function testFormatForPubSub()
    {
        $message = new JobRequest();
        $message->fixedTaskCount(1);
        $pubsub = $message->formatForPubSub();
        $this->assertIsArray($pubsub);
        $this->assertArrayHasKey('data', $pubsub);
        $this->assertIsString($pubsub['data']);
    }

    public function testPayload()
    {
        $message = new JobRequest();
        $message->fixedTaskCount(1);
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
