<?php

namespace App\Classes\EventHandlers;

use App\Classes\DataHelper;

class SubscriptionDays implements EventInterface
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
                'count_days' => $data['days'],
                'day_word' => DataHelper::num2word($data['days'], ['день', 'дня', 'дней']),
                'lang' => $user['lang'] ?? 'ru',
            ];
        }

        return $result;
    }
}
