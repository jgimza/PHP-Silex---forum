<?php
/**
 * Community repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class CommunityRepository.
 *
 * @package Repository
 */

class CommunityRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */

    protected $db;
    /**
     * CommunityRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch all records.
     *
     * @return array Result
     */

    public function findAll()
    {
        $queryBuilder = $this->queryAll();
        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Find user by UserID.
     *
     * @param int $id
     * @return array|mixed
     */

    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('t.idForumUser = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Find all user data including personal data.
     *
     * @param int $id
     * @return array|mixed
     */

    public function findData($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.username', 'u.idForumUser', 'u.blocked', 'u.idForumUserRole as role', 'd.name', 'd.surname', 'd.email', 'd.birthdate')
            ->from('forum_user', 'u')
            ->leftjoin('u', 'forum_user_data', 'd', 'u.idForumUser = d.idForumUser')
            ->where('u.idForumUser = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();
        return !$result ? [] : $result;
    }

    /**
     * Find count of user posts.
     *
     * @param int $id
     * @return array|mixed
     */

    public function findUserPosts($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.idForumUser', 'count(d.idForumPost) as posts', 'd.idForumUser')
            ->from('forum_user', 'u')
            ->leftjoin('u', 'forum_post', 'd', 'u.idForumUser = d.idForumUser')
            ->where('u.idForumUser = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();
        return !$result ? [] : $result;
    }

    /**
     * Query all records.
     *
     * @return $this
     */

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        return $queryBuilder->select('t.idForumUser', 't.username')
            ->from('forum_user', 't');
    }
}