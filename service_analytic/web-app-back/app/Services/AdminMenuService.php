<?php

namespace App\Services;

/**
 * Class AdminMenuService
 * Create admin menu data
 *
 * @package App\Services
 */
class AdminMenuService
{
    /**
     * Get menu data
     *
     * @return array[]
     */
    public static function getMenu()
    {
        $userPermissions = self::getUserPermissions();

        return [
            [
                'name' => 'Dashboard',
                'key' => 'dashboard',
                'title' => 'Dashboard',
                'classes' => 'nav-link align-middle px-0 text-white text-decoration-none',
                'iconClasses' => 'fas fa-tachometer-alt fa-lg',
                'accept' => true,
                'route' => '/admin/dashboard',
                'disable' => false
            ],
            [
                'name' => 'Пользователи',
                'key' => 'users-submenu',
                'title' => 'Меню пользователи',
                'classes' => 'nav-link px-0 align-middle text-white text-decoration-none',
                'iconClasses' => 'fas fa-users fa-lg',
                'disable' => false,
                'accept' => true,
                'submenu' => [
                    [
                        'name' => 'Роли',
                        'key' => 'roles',
                        'title' => 'Журнал ролей',
                        'route' => '/admin/roles',
                        'classes' => 'nav-link px-0 align-middle text-white text-decoration-none',
                        'iconClasses' => 'fas fa-shield-alt fa-lg',
                        'disable' => false,
                        'accept' => $userPermissions['canShowRoles']
                    ],
                    [
                        'name' => 'Журнал',
                        'key' => 'users',
                        'title' => 'Журнал пользователей',
                        'route' => '/admin/users',
                        'classes' => 'nav-link px-0 align-middle text-white text-decoration-none',
                        'iconClasses' => 'fas fa-users fa-lg',
                        'disable' => false,
                        'accept' => $userPermissions['canShowUsers']
                    ]
                ]
            ],
            [
                'name' => 'Черный список',
                'key' => 'blackList',
                'title' => 'черный список',
                'classes' => 'nav-link align-middle px-0 text-white text-decoration-none',
                'iconClasses' => 'fas  fa-times-circle  fa-lg',
                'accept' => true,
                'route' => '/admin/black_list',
                'disable' => false
            ]
        ];
    }

    /**
     * Get user permissions from check menu links is visible
     *
     * @return array
     */
    public static function getUserPermissions()
    {
        $userPermissions = auth()->user()->getPermissionsViaRoles();

        return [
            'canShowUsers' => true,
            'canShowRoles' => true
        ];
    }
}
