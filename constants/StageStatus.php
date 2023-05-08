<?php

namespace constant;

class StageStatus
{
    const NEW = 'NEW';
    const PLANNED = 'PLANNED';
    const DELETED = 'DELETED';

    public static array $validatedStatus = [
        self::NEW => 'NEW',
        self::PLANNED => 'PLANED',
        self::DELETED => 'DELETED'
    ];
}