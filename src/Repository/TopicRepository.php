<?php
/**
 * Topic repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class TopicRepository.
 *
 * @package Repository
 */

class TopicRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */

    protected $db;

    /**
     * TopicRepository constructor.
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
     * Find single topic by ID.
     *
     * @param int $id
     * @return array|mixed
     */

    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('t.idForumTopic = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();
        return !$result ? [] : $result;
    }

    /**
     * Check if topic is open or closed.
     *
     * @param int $id
     * @return array|mixed
     */

    public function findIfOpen($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('t.open')
            ->from('forum_topic', 't')
            ->where('t.idForumTopic = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();
        return !$result ? [] : $result;
    }

    /**
     * Find post data and post creator data.
     *
     * @param int $id
     * @return array
     */

    public function findPostData($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('p.idForumPost', 'p.content', 'p.idForumUser', 'p.createdAt as created', 't.idForumTopic', 't.idForumSection', 't.nameTopic')
            ->from('forum_post', 'p')
            ->leftjoin('p', 'forum_topic', 't', 'p.idForumTopic = t.idForumTopic')
            ->leftjoin('p', 'forum_user', 'u', 'p.idForumUser = u.idForumUser')
            ->where('t.idForumTopic = :id')
            ->groupby('idForumPost')
            ->addselect('u.username', 'u.blocked')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();
        return !$result ? [] : $result;
    }

    /**
     * Find number of posts in topic.
     *
     * @param int $id
     * @return array
     */

    public function findNofPosts($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('t.idForumTopic', 't.nameTopic', 'idForumSection', 'count(s.idForumPost) AS post')
            ->from('forum_topic', 't')
            ->leftjoin('t', 'forum_post', 's', 't.idForumTopic = s.idForumTopic')
            ->groupBy('t.idForumTopic')
            ->where('t.idForumSection =:id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();
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
        return $queryBuilder->select('t.nameTopic', 't.open', 't.idForumSection', 't.idForumTopic', 's.idForumSection')
            ->from('forum_topic', 't')
            ->leftjoin('t', 'forum_section', 's', 't.idForumSection = s.idForumSection');
    }

    /**
     * Find section name.
     *
     * @param int $id
     * @return array
     */

    public function findSectionName($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('t.idForumSection', 't.nameSection as name')
            ->from('forum_section', 't')
            ->where('t.idForumSection =:id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();
        return !$result ? [] : $result;
    }

    /**
     * Find count of user posts.
     *
     * @return array
     */

    public function findUserPosts()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('u.idForumUser', 'count(d.idForumPost) as posts', 'd.idForumUser')
            ->from('forum_user', 'u')
            ->leftjoin('u', 'forum_post', 'd', 'u.idForumUser = d.idForumUser')
            ->where('u.idForumUser = d.idForumUser')
            ->groupby('u.idForumUser');
        $result = $queryBuilder->execute()->fetchAll();
        return !$result ? [] : $result;
    }

    /**
     * Add topic data to database.
     *
     * @param $data
     */

    public function add($data)
    {
        $this->db->insert('forum_topic', $data);
    }

    /**
     * Edit topic data.
     *
     * @param $data
     */

    public function edit($data)
    {
        $this->db->update('forum_topic', $data, ['idForumTopic' => $data['idForumTopic']]);
    }

    /**
     * Delete topic data.
     *
     * @param int $id
     */

    public function delete($id)
    {
        $this->db->delete('forum_topic', ['idForumTopic' => $id]);
    }

    /**
     * Close topic.
     *
     * @param int $id
     */

    public function closeTopic($id)
    {
        $this->db->update('forum_topic', ['open' => 0], ['idForumTopic' => $id]);
    }

    /**
     * Add post data.
     *
     * @param $data
     */

    public function addpost($data)
    {
        $this->db->insert('forum_post', $data);
    }
}