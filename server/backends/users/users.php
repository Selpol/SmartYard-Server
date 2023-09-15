<?php

namespace backends\users;

use backends\backend;

abstract class users extends backend
{
    abstract public function getUsers(): bool|array;

    abstract public function getUser(int $uid): bool|array;

    abstract public function getUidByEMail(string $eMail): bool|int;

    abstract public function getUidByLogin(string $login): int|bool;

    abstract public function addUser(string $login, ?string $realName = null, ?string $eMail = null, ?string $phone = null): int|bool;

    abstract public function setPassword(int $uid, string $password): bool;

    abstract public function deleteUser(int $uid): bool;

    abstract public function modifyUserEnabled(int $uid, bool $enabled): bool;

    abstract public function modifyUser(int $uid, string $realName = '', string $eMail = '', string $phone = '', string $tg = '', string $notification = 'tgEmail', bool $enabled = true, string $defaultRoute = '', string|bool $persistentToken = false, int $primaryGroup = -1): bool;
}
