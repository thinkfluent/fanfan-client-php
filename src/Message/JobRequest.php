<?php

declare(strict_types=1);

namespace FanFan\Client\Message;

class JobRequest implements \JsonSerializable
{
    use HasPayloadTrait;

    public const 
        FANOUT_TYPE_FIXED = 'FIXED',
        FANOUT_TYPE_RANGE = 'RANGE',
        FANOUT_TYPE_FOREACH = 'FOREACH';

    protected string $action = 'default';
    protected ?int $fixedTaskCount = null;
    protected ?array $forEachItems = null;
    protected ?string $forEachAs = null;
    protected ?string $taskTopic = null;
    protected ?string $doneTopic = null;
    protected array $taskAttributes = [];
    protected ?string $rangeAs = null;
    protected ?int $rangeStart = null;
    protected ?int $rangeStop = null;
    protected ?int $rangeStep = null;

    private string $fanoutType;


    public function fixedTaskCount(int $taskCount): self
    {
        $this->fanoutType = self::FANOUT_TYPE_FIXED;
        $this->fixedTaskCount = $taskCount;
        return $this;
    }

    public function forEach(string $as, array $items): self
    {
        $this->fanoutType = self::FANOUT_TYPE_FOREACH;
        $this->forEachAs = $as;
        $this->forEachItems = $items;
        return $this;
    }

    public function range(string $as, int $start, int $stop, int $step = 1): self
    {
        $this->fanoutType = self::FANOUT_TYPE_RANGE;
        $this->rangeAs = $as;
        $this->rangeStart = $start;
        $this->rangeStop = $stop;
        $this->rangeStep = $step;
        return $this;
    }

    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function taskTopic(string $topic): self
    {
        $this->taskTopic = $topic;
        return $this;
    }

    public function doneTopic(string $topic): self
    {
        $this->doneTopic = $topic;
        return $this;
    }

    public function taskAttributes(array $attribs): self
    {
        $this->taskAttributes = $attribs;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getTaskTopic(): ?string
    {
        return $this->taskTopic;
    }

    public function getDoneTopic(): ?string
    {
        return $this->doneTopic;
    }

    public function getTaskAttributes(): array
    {
        return $this->taskAttributes;
    }

    public function getFanoutType(): string
    {
        return $this->fanoutType;
    }

    public function getFanoutConfig(): array
    {
        switch ($this->fanoutType ?? 'unknown') {
            case self::FANOUT_TYPE_FIXED:
                return ['tasks' => $this->fixedTaskCount];
            case self::FANOUT_TYPE_FOREACH:
                return ['foreach' => [
                    'as' => $this->forEachAs,
                    'items' => $this->forEachItems,
                ]];
            case self::FANOUT_TYPE_RANGE:
                return ['range' => [
                    'as' => $this->rangeAs,
                    'start' => $this->rangeStart,
                    'stop' => $this->rangeStop,
                    'step' => $this->rangeStep,
                ]];
        }
        throw new \InvalidArgumentException('At least one of tasks/foreach/range required to fan out.');
    }

    /**
     * Produce an array in the correct for publishing to PubSub via the Google API Client
     * [Message Format](https://cloud.google.com/pubsub/docs/reference/rest/v1/PubsubMessage).
     *
     * @return array
     */
    public function formatForPubSub(): array
    {
        return [
            'data' => \json_encode($this->jsonSerialize()),
        ];
    }

    public function jsonSerialize(): array
    {
        $data = [
            'action' => $this->action,
            'fanout' => $this->getFanoutConfig(),
        ];
        // Optional payload
        if (!empty($this->payload)) {
            $data['payload'] = $this->payload;
        }
        // Optional custom topic & attributes for the eventually produced Task messages
        if (!empty($this->taskTopic)) {
            $data['taskTopic'] = $this->taskTopic;
        }
        if (!empty($this->taskAttributes)) {
            $data['taskAttributes'] = $this->taskAttributes;
        }
        // Optional custom topic for the final JobOutcome message
        if (!empty($this->doneTopic)) {
            $data['doneTopic'] = $this->doneTopic;
        }
        return $data;
    }
}
