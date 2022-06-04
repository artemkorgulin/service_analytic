<?php

namespace App\Classes\EventHandlers;

class CompanyCorporateEnd implements EventInterface
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
                'date' => $data['date'],
                'company' => $data['company'],
                'lang' => $user['lang'] ?? 'ru',
            ];
        }

        return $result;
    }
}
