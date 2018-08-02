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
class SectionRepository
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
    public function findAll()
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
        $queryBuilder->where('t.idForumSection = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    public function findTopicData()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('s.idForumSection', 's.nameSection', 's.idSubforum', 'count(DISTINCT t.idForumTopic) as topics', 't.idForumSection')
            ->from('forum_section', 's')
            ->leftjoin('s', 'forum_topic', 't', 's.idForumSection = t.idForumSection')
            ->leftjoin('t', 'forum_post', 'p', 't.idForumTopic = p.idForumTopic')
            ->where('s.idForumSection = t.idForumSection')
            ->groupby('s.idForumSection')
            ->addselect('count(p.idForumPost) as posts');
        $result = $queryBuilder->execute()->fetchAll();
        return !$result ? [] : $result;
    }

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('s.name', 's.idSubforum as idSub', 't.idForumSection', 't.nameSection', 't.idSubforum')
            ->from('forum_section', 't')
            ->join('t', 'forum_subforum', 's', 's.idSubforum = t.idSubforum');

    }


}