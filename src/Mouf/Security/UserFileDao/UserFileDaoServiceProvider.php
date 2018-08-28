<?php


namespace Mouf\Security\UserFileDao;


use Psr\Container\ContainerInterface;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\ServiceProvider;
use Mouf\Security\UserService\UserDaoInterface;

class UserFileDaoServiceProvider extends ServiceProvider
{
    /**
     * @Factory(aliases={UserDaoInterface::class})
     */
    public static function createUserFileDao(ContainerInterface $container): UserFileDao
    {
        return new UserFileDao($container->get('userFile'));
    }
}
