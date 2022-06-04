<?php

namespace App\Classes\EventHandlers;

class MarketplaceStartUpload implements EventInterface
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
                'lang' => $user['lang'] ?? 'ru',
                'marketplace' => $data['marketplace'],
            ];
        }

        return $result;
    }
}
