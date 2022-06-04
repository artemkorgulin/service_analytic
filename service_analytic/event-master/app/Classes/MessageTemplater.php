<?php

namespace App\Classes;

use App\Classes\Templater\TemplaterInterface;
use Mustache_Engine;

class MessageTemplater
{
    /**
     * Получить сформированное сообщение.
     *
     * @param  string  $template
     * @param  array  $params
     * @return string
     */
    public function getMessage(string $template, array $params): string
    {
        $engine = new Mustache_Engine([
            'escape' => function ($value) {
                return $value;
            },
        ]);
        return $engine->render($template, $params);
    }

    /**
     * Преобразовать html код в сообщения по логике объекта
     *
     * @param TemplaterInterface $templater
     * @param string $html
     * @return string
     */
    public function htmlToMessage(TemplaterInterface $templater, string $html): string
    {
        return $templater->htmlToMessage($html);
    }
}
