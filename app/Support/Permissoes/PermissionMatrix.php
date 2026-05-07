<?php

namespace App\Support\Permissoes;

class PermissionMatrix
{
    public static function roleDefaults(string $role): array
    {
        $defaults = config('permissions.role_defaults', []);

        if (! isset($defaults[$role]) || ! is_array($defaults[$role])) {
            return [];
        }

        return array_values(array_filter($defaults[$role], function ($item) {
            return is_string($item) && trim($item) !== '';
        }));
    }

    public static function allows(string $permission, array $grants): bool
    {
        $permission = trim($permission);
        if ($permission === '') {
            return false;
        }

        foreach ($grants as $grant) {
            if (! is_string($grant) || $grant === '') {
                continue;
            }

            if ($grant === '*') {
                return true;
            }

            if ($grant === $permission) {
                return true;
            }

            if (str_ends_with($grant, '*')) {
                $prefix = rtrim($grant, '*');
                if ($prefix !== '' && str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
