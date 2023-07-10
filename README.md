# PHP Client for FanFan - a Serverless Fan-out, Fan-in Framework for Google Cloud Pub/Sub

For more detail on FanFan, see the main project here: [thinkfluent/fanfan](https://github.com/thinkfluent/fanfan)

If you want to see a working demo application, see this project: [thinkfluent/fanfan-demo-php](https://github.com/thinkfluent/fanfan-demo-php)

## Installation

```bash
composer require thinkfluent/fanfan-client-php
```

## Usage

I recommend you review & understand the message & workflow concepts first here: [thinkfluent/fanfan](https://github.com/thinkfluent/fanfan)

### Create a Fan-out Job

Note: The `fanfan-client-php` library does not require or operate on the the Google Pub/Sub client package. You will 
need to include that yourself, and emit messages to it.

```php
use FanFan\Client\Message\JobRequest;
use Google\Cloud\PubSub\PubSubClient;

// Prepare a JobRequest
$jobRequest = (new JobRequest())
    ->action('process_order')
    ->forEach('order', [4, 2, 19, 79]);

// Send to FanFan (via Pub/Sub)
$jobRequestTopic = (new PubSubClient())->topic('projects/get-fanfan/topics/fanfan-job-request');
$jobRequestTopic->publish($jobRequest->formatForPubSub());
```

### Execute a Single Fanned-out Task & Emit Response
Using a simple "HTTP Push" subscription. Your application will receive 1-many requests like this

```php
use FanFan\Client\Message\Builder\TaskBuilder;
use Google\Cloud\PubSub\PubSubClient;

// Grab the Task data from the POST body (most of this is extracting the base64 message from the Pub/Sub payload)
// Your framework of choice may well have tooling to support you with this (Like PSR-7 Requests)
$payload = \json_decode(\file_get_contents('php://input'), false, 512, JSON_THROW_ON_ERROR);
$messageData = \json_decode(\base64_decode($payload->message->data), false, 512, JSON_THROW_ON_ERROR);

// Build `FanFan\Client\Message\Task`, contains instructions & payload for ONE fanned-out task
$task = TaskBuilder::fromPubSub($messageData);

// ...do work here...
// e.g. switch on $task->getAction(), use the data from $task->getPayload();

// Create & publish the Task outcome
$outcome = $task->createOutcome(TaskStatus::SUCCEEDED);
$jobRequestTopic = (new PubSubClient())->topic('projects/get-fanfan/topics/fanfan-task-done');
$jobRequestTopic->publish($outcome->formatForPubSub());
```

### Receive Job Outcome
```php
use FanFan\Client\Message\Builder\JobOutcomeBuilder;
// Extract Pub/Sub message into `$messageData` as per above example
$payload = \json_decode(\file_get_contents('php://input'), false, 512, JSON_THROW_ON_ERROR);
$messageData = \json_decode(\base64_decode($payload->message->data), false, 512, JSON_THROW_ON_ERROR);

// Build `FanFan\Client\Message\JobOutcome`
$outcome = JobOutcomeBuilder::fromPubSub($messageData);

// Work with the output
echo $outcome->getStatus(), PHP_EOL;
echo $outcome->getTaskCounts()['SUCCEEDED'], PHP_EOL;
echo $outcome->getTaskCounts()['FAILED'], PHP_EOL;
```