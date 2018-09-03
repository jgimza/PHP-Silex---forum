<?php
/**
 * Role repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class RoleRepository.
 *
 * @package Repository
 */

class RoleRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */

    protected $db;

    /**
     * RoleRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getUserID()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('idForumUserRole')
            ->from('forum_userrole')
            ->where('name = :name')
            ->setParameter(':name', "ROLE_USER", \PDO::PARAM_STR);
        return $queryBuilder->execute()->fetch()['idForumUserRole'];
    }

    public function getAdminID()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('idForumUserRole')
            ->from('forum_userrole')
            ->where('name = :name')
            ->setParameter(':name', "ROLE_ADMIN", \PDO::PARAM_STR);
        return $queryBuilder->execute()->fetch()['idForumUserRole'];
    }
}