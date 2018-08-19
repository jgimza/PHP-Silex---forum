<?php
/**
 * Tag repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class TagRepository.
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
     * TagRepository constructor.
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
    public function findAll($id)
    {
        $queryBuilder = $this->queryAll();
        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */

    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('t.idForumTopic = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }


    public function findPostData($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('p.idForumPost', 'p.content', 'p.idForumUser', 't.idForumTopic', 't.idForumSection', 't.nameTopic')
            ->from('forum_post', 'p')
            ->leftjoin('p', 'forum_topic', 't', 'p.idForumTopic = t.idForumTopic')
            ->leftjoin('p', 'forum_user', 'u', 'p.idForumUser = u.idForumUser')
            ->where('t.idForumTopic = :id')
            ->groupby('idForumPost')
            ->addselect('u.username')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();
        return !$result ? [] : $result;
    }

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

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('t.nameTopic', 't.idForumSection', 't.idForumTopic', 's.idForumSection')
            ->from('forum_topic', 't')
            ->leftjoin('t', 'forum_section', 's', 't.idForumSection = s.idForumSection');

    }

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

    public function add($data)
    {
        $this->db->insert('forum_topic', $data);
    }

    public function edit($data)
    {
        $this->db->update('forum_topic', $data, ['idForumTopic' => $data['idForumTopic']]);
    }

    public function delete($id)
    {
        $this->db->delete('forum_topic', ['idForumTopic' => $id]);
    }
}