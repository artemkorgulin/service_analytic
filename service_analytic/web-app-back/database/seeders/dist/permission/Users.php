<?php

namespace Database\Seeders\dist\permission;

use App\Models\User;

/**
 *
 */
class Users implements IPermission
{
    /**
     * @var User[]|\Illuminate\Database\Eloquent\Collection
     */
    protected $users;
    /**
     * @var
     */
    protected $roles;
    /**
     * @var
     */
    protected $permissions;

    /**
     *
     */
    public function __construct()
    {
        $this->users = User::all();
    }

    /**
     * @param $json
     */
    public function setJson($json)
    {
        $this->roles = $json['roles'];
        $this->permissions = $json['permissions'];
    }

    /**
     *
     */
    private function createAdmin(){
        $user = User::where('email', '=', 'toyij39180@tripaco.com')->first();

        if (!empty($user)) {
            \App\Models\ModelHasPermission::query()->where(['model_id' => $user->id])->delete();
            $user->assignRole('admin');
        }
    }

    /**
     *
     */
    public function build()
    {
        foreach($this->users as $user){
            $user->assignRole( 'user');
        }
        $this->createAdmin();
    }
}
