<?php

namespace App\Nova\Actions;

use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ForgetAccountsCache extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Очистить кэш';


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $users)
    {
        $userService = new UserService();
        foreach ($users as $user) {
            $userService->setUser($user)->forgetAccountsCache();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
