<?php

namespace App\Classes\EventHandlers;

class BillingBank implements EventInterface
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

        foreach ($data['order'] as $good) {
            foreach ($users as $user) {
                $result[] = [
                    'user_ids' => [$user['id']],
                    'price' => $good['price'],
                    'fio' => $user['name'] ?? '',
                    'lang' => $user['lang'] ?? 'ru',
                ];
            }
        }

        return $result;
    }
}
