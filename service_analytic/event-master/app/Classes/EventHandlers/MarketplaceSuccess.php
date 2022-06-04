<?php

namespace App\Classes\EventHandlers;

use App\Classes\DataHelper;

class MarketplaceSuccess implements EventInterface
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
                'counted' => $data['counted'],
                'product' => DataHelper::num2word($data['counted'], ['товар', 'товара', 'товаров']),
                'lang' => $user['lang'] ?? 'ru',
                'marketplace' => $data['marketplace'],
            ];
        }

        return $result;
    }
}
