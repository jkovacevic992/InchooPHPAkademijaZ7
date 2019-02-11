<?php

/**
 * @method setId($id)
 * @method setContent($content)
 * @method setUser($user)
 * @method setLikes($likes)
 * @method setDate($date)
 * @method setComments($comments)
 * @method setUserid($userid)
 * @method setTags($tags)
 */
class Post
{
    private $id;

    private $content;

    private $user;

    private $date;

    private $likes;

    private $comments;

    private $userid;

    private $tags;

    private $dislikes;

    public function __construct($id, $content, $user, $date, $likes, $comments, $userid, $tags = null, $dislikes)
    {
        $this->setId($id);
        $this->setContent($content);
        $this->setUser($user);
        $this->setDate($date);
        $this->setLikes($likes);
        $this->setComments($comments);
        $this->setUserid($userid);
        $this->setTags($tags);
        $this->setDislikes($dislikes);
    }

    public static function all()
    {


        $list = [];
        $db = Db::connect();
        $statement = $db->prepare("select 
        a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date, 
        count(distinct c.id) as likes, count(distinct d.id) as dislikes
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post
        left join dislikes d on a.id=d.post
        
        where a.date > ADDDATE(now(), INTERVAL -7 DAY) and not a.hidden=true
        group by a.id, a.content, concat(b.firstname, ' ', b.lastname), a.date 
        having count(distinct d.id)<5
        order by a.date desc limit 10");
        $statement->execute();
        foreach ($statement->fetchAll() as $post) {

            $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $comments = $statement->fetchAll();

            $list[] = new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, 0, [], $post->dislikes);

        }


        return $list;
    }
    public static function hiddenPosts($id)
    {
        $list = [];
        $db = Db::connect();
        $statement = $db->prepare("select
  a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date,
  count(distinct c.id) as likes, count(distinct d.id) as dislikes
from
  post a inner join user b on a.user=b.id
         left join likes c on a.id=c.post
         left join dislikes d on a.id=d.post

where  a.hidden=true and b.id=:id
group by a.id, a.content, concat(b.firstname, ' ', b.lastname), a.date
having count(distinct d.id)<5
order by a.date desc");
        $statement->bindValue('id',$id);
        $statement->execute();
        foreach ($statement->fetchAll() as $post) {

            $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $comments = $statement->fetchAll();

            $list[] = new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, 0, [], $post->dislikes);
        }
        return $list;
    }

    public static function find($id)
    {
        $id = intval($id);
        $db = Db::connect();
        $db->beginTransaction();
        $statement = $db->prepare("select 
        a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date, a.user as userid, count(distinct  c.id) as likes, count(distinct d.id) as dislikes
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post 
        left join dislikes d on a.id=d.post
         where a.id=:id");
        $statement->bindValue('id', $id);
        $statement->execute();
        $post = $statement->fetch();

        $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date, count(c.id) as dislikes from comment a
        inner join user b on a.user=b.id
        left join dislikes c on c.comment=a.id
        where a.post=1
        group by a.id having count(c.id)<5");
        $statement->bindValue('id', $id);
        $statement->execute();
        $comments = $statement->fetchAll();

        $statement = $db->prepare("select content from tag where post=:id");
        $statement->bindValue('id', $id);
        $statement->execute();
        $tags = $statement->fetchAll();
        if (!empty($tags)) {
            $tags = explode(',', $tags[0]->content);
        }


        $db->commit();
        return new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, $post->userid, $tags, $post->dislikes);
    }

    public static function findLikes($id)
    {
        $id = intval($id);
        $db = Db::connect();
        $db->beginTransaction();
        $statement = $db->prepare("select a.id, concat(b.firstname, ' ', b.lastname) as user
from likes a inner join user b on a.user = b.id
where a.post=:id");
        $statement->bindValue('id',$id);
        $statement->execute();
        $likes = $statement->fetchAll();

        return $likes;
    }

    /**
     * @return mixed
     */


    public function __call($name, $arguments)
    {
        $function = substr($name, 0, 3);
        if ($function === 'set') {
            $this->__set(strtolower(substr($name, 3)), $arguments[0]);
            return $this;
        } else if ($function === 'get') {
            return $this->__get(strtolower(substr($name, 3)));
        }

        return $this;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}