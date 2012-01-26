<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * UsrListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsrListRepository extends EntityRepository
{
  /**
   * Get all lists readable for a given User
   *
   * @param \User_Adapter $user
   * @return \Doctrine\Common\Collections\ArrayCollection
   */
  public function findUserLists(\User_Adapter $user)
  {
    $dql = 'SELECT l FROM Entities\UsrList l
              JOIN l.owners o
            WHERE o.usr_id = :usr_id';


    $params = array(
        'usr_id' => $user->get_id(),
    );

    $query = $this->_em->createQuery($dql);
    $query->setParameters($params);

    return $query->getResult();
  }

  /**
   *
   * @param \User_Adapter $user
   * @param type $list_id
   * @return \Entities\UsrList
   */
  public function findUserListByUserAndId(\User_Adapter $user, $list_id)
  {
    $list = $this->find($list_id);

    /* @var $basket \Entities\UsrList */
    if (null === $list)
    {
      throw new \Exception_NotFound(_('List is not found'));
    }

    if (!$list->hasAccess($user))
    {
      throw new \Exception_Forbidden(_('You have not access to this list'));
    }

    return $list;
  }

  /**
   * Search for a UsrList like '' with a given value, for a user
   *
   * @param \User_Adapter $user
   * @param type $name
   * @return \Doctrine\Common\Collections\ArrayCollection
   */
  public function findUserListLike(\User_Adapter $user, $name)
  {
    $dql = 'SELECT l FROM Entities\UsrList l
              JOIN l.owners o
            WHERE o.usr_id = :usr_id AND l.name LIKE :name';

    $params = array(
        'usr_id' => $user->get_id(),
        'name' => $name.'%'
    );

    $query = $this->_em->createQuery($dql);
    $query->setParameters($params);

    return $query->getResult();
  }

}
