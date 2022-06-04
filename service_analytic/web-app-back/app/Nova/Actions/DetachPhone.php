<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

use App\Models\User;
use App\Services\PhoneService;

class DetachPhone extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Отвязать телефон';


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  Collection  $users
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $users)
    {
        $users = User::whereIn('id', $users->pluck('id'))->get();
        $phoneService = new PhoneService();
        $updatedCount = 0;
        foreach ($users as $user) {
            $updatedCount++;
            $phoneService->detachPhone($user);
            $user->save();
        }

        if ($updatedCount === 1) {
            return Action::message(__('User :name has been successfully detached', ['name' => $users->first()->name]));
        } elseif (is_int($updatedCount) && $updatedCount !== 0) {
            return Action::message(__('Users have been successfully detached'));
        } else {
            return Action::danger(__('No user has been detached'));
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
