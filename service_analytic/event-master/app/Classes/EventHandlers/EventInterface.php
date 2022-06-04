<?php

namespace App\Classes\EventHandlers;

interface EventInterface
{
    public function getParams(array $users, array $data): array;
}
