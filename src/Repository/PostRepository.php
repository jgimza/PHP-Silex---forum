<?php
/**
 * Post repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class PostRepository.
 *
 */

class PostRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */

    protected $db;

    /**
     * PostRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Add post data.
     *
     * @param int $data
     */
    public function add($data)
    {
        $this->db->insert('forum_post', $data);
    }

    /**
     * Find one post by PostID.
     *
     * @param int $id
     *
     * @return array|mixed
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('t.idForumPost = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Edit post data.
     *
     * @param int $data
     */
    public function edit($data)
    {
        $this->db->update('forum_post', $data, ['idForumPost' => $data['idForumPost']]);
    }

    /**
     * Delete post data.
     *
     * @param int $id
     */
    public function delete($id)
    {
        $this->db->delete('forum_post', ['idForumPost' => $id]);
    }

    /**
     * Query all records.
     *
     * @return $this
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('t.content', 't.idForumUser', 't.idForumPost', 't.idForumTopic')
            ->from('forum_post', 't');
    }
}
