<?php declare(strict_types=1);

namespace Selpol\Entity\Model\Core;

use Selpol\Entity\Model\Permission;
use Selpol\Entity\Model\Role;
use Selpol\Entity\Repository\Core\CoreUserRepository;
use Selpol\Framework\Entity\Entity;
use Selpol\Framework\Entity\Relationship\ManyToManyRelationship;
use Selpol\Framework\Entity\Trait\RelationshipTrait;
use Selpol\Framework\Entity\Trait\RepositoryTrait;

/**
 * @property int $uid
 *
 * @property string $login
 * @property string $password
 *
 * @property int<0, 1> $enabled
 *
 * @property string|null $aud_jti
 *
 * @property string|null $real_name
 * @property string|null $e_mail
 * @property string|null $phone
 * @property string|null $tg
 * @property string|null $notification
 * @property string|null $default_route
 *
 * @property int|null $last_login
 *
 * @property-read Role[] $roles
 * @property-read Permission[] $permissions
 */
class CoreUser extends Entity
{
    /**
     * @use RepositoryTrait<CoreUserRepository>
     */
    use RepositoryTrait;
    use RelationshipTrait;

    public static string $table = 'core_users';

    public static string $columnId = 'uid';

    public function jsonSerialize(): array
    {
        $value = $this->getValue();

        if (array_key_exists('password', $value)) {
            unset($value['password']);
        }

        return $value;
    }

    /**
     * @return ManyToManyRelationship<Role>
     */
    public function roles(): ManyToManyRelationship
    {
        return $this->manyToMany(Role::class, 'user_role', localRelation: 'user_id', foreignRelation: 'role_id');
    }

    /**
     * @return ManyToManyRelationship<Permission>
     */
    public function permissions(): ManyToManyRelationship
    {
        return $this->manyToMany(Permission::class, 'user_permission', localRelation: 'user_id', foreignRelation: 'permission_id');
    }

    public static function getColumns(): array
    {
        return [
            static::$columnId => rule()->id(),

            'login' => rule()->required()->string()->nonNullable(),
            'password' => rule()->required()->string()->nonNullable(),

            'enabled' => rule()->required()->int()->nonNullable(),

            'aud_jti' => rule()->string(),

            'real_name' => rule()->string(),
            'e_mail' => rule()->string(),
            'phone' => rule()->string(),
            'tg' => rule()->string(),
            'notification' => rule()->string(),
            'default_route' => rule()->string(),

            'last_login' => rule()->int()
        ];
    }
}