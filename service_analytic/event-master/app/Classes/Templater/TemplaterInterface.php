<?php

namespace App\Classes\Templater;

interface TemplaterInterface
{
    public function htmlToMessage(string $html): string;
}
