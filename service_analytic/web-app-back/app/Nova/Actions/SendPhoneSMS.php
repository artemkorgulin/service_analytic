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

class SendPhoneSMS extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Отправить человеку SMS с кодом подтверждения';


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
        $users = User::whereIn('id', $users->pluck('id'))->whereNotNull('unverified_phone')->get();
        $phoneService = new PhoneService();
        $updatedCount = 0;
        foreach ($users as $user) {
            if ($phoneService->needPhoneConfirmation($user)) {
                $user->issueNewPhoneVerificationToken();
                $user->save();
                $phoneService->sendPhoneConfirmation($user);
                $updatedCount++;
            }
        }

        if ($updatedCount === 1) {
            return Action::message(__('Verification SMS was sended to :name', ['name' => $users->first()->name]));
        } elseif (is_int($updatedCount) && $updatedCount !== 0) {
            return Action::message(__('All verification SMS was sended'));
        } else {
            return Action::danger(__('No SMS was sended'));
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
