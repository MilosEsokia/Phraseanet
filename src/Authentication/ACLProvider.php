<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Authentication;

use App\Entity\User;
use Silex\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ACLProvider
{
    /**
     * An array cache for ACL's.
     *
     * @var array
     */
    private static $cache = [];

    //private $app;

//    public function __construct(Application $app)
//    {
//        $this->app = $app;
//    }

    /**
     * Gets ACL for user.
     *
     * @param User $user
     *
     * @return \App\Utils\ACL
     */
    public function get(User $user)
    {
        if (null !== $acl = $this->fetchFromCache($user)) {
            return $acl;
        }

        return $this->fetch($user);
    }

    /**
     * Purges ACL cache
     */
    public static function purge()
    {
        self::$cache = [];
    }

    /**
     * Fetchs ACL from cache for users.
     *
     * @param User $user
     *
     * @return null || \App\Utils\ACL
     */
    private function fetchFromCache(User $user)
    {
        return $this->hasCache($user) ? self::$cache[$user->getId()] : null;
    }

    /**
     * Tells whether ACL for user is already cached.
     *
     * @param User $user
     *
     * @return boolean
     */
    private function hasCache(User $user)
    {
        return isset(self::$cache[$user->getId()]);
    }

    /**
     * Saves user's ACL in cache and returns it.
     *
     * @param User $user
     *
     * @return \App\Utils\ACL
     */
    private function fetch(User $user)
    {
        return self::$cache[$user->getId()] = new \App\Utils\ACL($user, $this->app);
    }
}
