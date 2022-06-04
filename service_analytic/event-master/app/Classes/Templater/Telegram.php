<?php

namespace App\Classes\Templater;

class Telegram implements TemplaterInterface
{
    private const REPLACE_TEMPLATE_TELEGRAM = [
        /** Ссылки */
        '/<a\s+href\s*=\s*([\"\'])+(.*)\1+>(.*)<\/a>/mi' => '$3: $2',
    ];

    public function htmlToMessage(string $message): string
    {
        $patterns = array_keys(self::REPLACE_TEMPLATE_TELEGRAM);
        $replaces = array_values(self::REPLACE_TEMPLATE_TELEGRAM);

        $message = preg_replace($patterns, $replaces, $message);

        return strip_tags($message);
    }
}
