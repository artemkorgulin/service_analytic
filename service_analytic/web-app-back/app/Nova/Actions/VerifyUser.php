<?php

namespace App\Nova\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class VerifyUser extends Action
{

    use InteractsWithQueue, Queueable;

    public $name = 'Подтвердить email';


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
        /** @var User $user */
        $updatedCount = User::whereIn('id', $users->pluck('id'))
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => Carbon::now()]);

        if ($updatedCount === 1) {
            return Action::message(__('User :name has been successfully verified', ['name' => $users->first()->name]));
        } elseif (is_int($updatedCount) && $updatedCount !== 0) {
            return Action::message(__('Users have been successfully verified'));
        } else {
            return Action::danger(__('No user has been verified'));
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
