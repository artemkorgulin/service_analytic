<?php

namespace App\Classes\EventHandlers;

use stdClass;

class Hello implements EventInterface
{
    /**
     * Получить параметры для шаблонизатора.
     *
     * @param  array  $users
     * @param  array  $data
     * @return array
     */
    public function getParams(array $users, array $data): array
    {
        $result = [];

        foreach ($data['goods'] as $good) {
            foreach ($users as $user) {
                if (isset($result[$user['lang']])) {
                    $result[$user['lang']]['user_ids'][] = $user['id'];
                } else {
                    $result[$user['lang']] = [
                        'user_ids' => [$user['id']],
                        'title' => $good['title'],
                        'price' => $good['price'],
                        'lang' => $user['lang'],
                    ];
                }
            }
        }

        return $result;
    }
}
