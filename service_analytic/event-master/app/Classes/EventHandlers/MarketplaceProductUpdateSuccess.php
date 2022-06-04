<?php

namespace App\Classes\EventHandlers;

class MarketplaceProductUpdateSuccess implements EventInterface
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

        foreach ($users as $user) {
            $result[] = [
                'user_ids' => [$user['id']],
                'count' => $data['count'],
                'total' => $data['total'],
                'lang' => $user['lang'] ?? 'ru',
                'marketplace' => $data['marketplace'],
            ];
        }

        return $result;
    }
}
