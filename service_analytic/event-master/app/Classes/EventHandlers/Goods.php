<?php

namespace App\Classes\EventHandlers;

class Goods implements EventInterface
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
                $result[] = [
                    'user_ids' => [$user['id']],
                    'title' => $good['title'] ?? 'Test',
                    'price' => $good['price'] ?? 0,
                    'fio' => $user['name'],
                    'lang' => $user['lang'] ?? 'ru',
                ];
            }
        }

        return $result;
    }
}
