<?php

declare(strict_types=1);

namespace FanFan\Client;

interface TaskStatus
{
    public const
        PENDING = 'PENDING',
        RUNNING = 'RUNNING',
        SUCCEEDED = 'SUCCEEDED',
        FAILED = 'FAILED';

    public const DESCRIPTION = [
        self::PENDING => '',
        self::RUNNING => '',
        self::SUCCEEDED => 'All tasks completed',
        self::FAILED => 'At least one Task in the Job has failed',
    ];
}
