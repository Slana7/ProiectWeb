<?php
require_once __DIR__ . '/../services/UserService.php';

class UserController
{
    public static function getById(int $id): ?array
    {
        return UserService::getUserById($id);
    }

    public static function deleteById(int $id): bool
    {
        $result = UserService::deleteUserCompletely($id);
        return !empty($result['success']);
    }
}
