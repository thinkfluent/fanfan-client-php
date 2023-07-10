<?php

declare(strict_types=1);

namespace FanFan\Client;

interface JobStatus
{
    public const
        PENDING = 'PENDING',
        SCHEDULED = 'SCHEDULED',
        RUNNING = 'RUNNING',
        SUCCEEDED = 'SUCCEEDED',
        FAILED = 'FAILED'; // @todo Do we want "partial"?

    public const DESCRIPTION = [
        self::PENDING => 'A newly created/requested Job, before being accepted',
        self::SCHEDULED => 'Accepted & enqueued for work',
        self::RUNNING => 'One, but not all tasks in progress',
        self::SUCCEEDED => 'All tasks completed ',
        self::FAILED => 'At least one Task in the Job has failed',
    ];
}
