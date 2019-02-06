<?php

class IndexController
{
    public function index()
    {
        $view = new View();
        $posts = Post::all();

        $view->render('index', [
            "posts" => $posts
        ]);
    }

    public function view($id = 0)
    {
        $view = new View();

        $view->render('view', [
            "post" => Post::find($id)
        ]);
    }

    public function newPost()
    {
        $data = $this->_validate($_POST);

        if ($data === false) {
            header('Location: ' . App::config('url'));
        } else {
            $connection = Db::connect();
            $connection->beginTransaction();
            $stmt = $connection->prepare('INSERT INTO post (content,user) VALUES (:content,:user)');
            $stmt->bindValue('content', $data['content']);
            $stmt->bindValue('user', Session::getInstance()->getUser()->id);
            $stmt->execute();

            $stmt = $connection->prepare('INSERT INTO tag(content,post) value (:content,last_insert_id())');
            $stmt->bindValue('content', $data['tag']);
            $stmt->execute();
            $connection->commit();

            header('Location: ' . App::config('url'));
        }
    }

    /**
     * @param $data
     * @return array|bool
     */
    private function _validate($data)
    {
        $required = ['content'];

        //validate required keys
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                return false;
            }

            $data[$key] = trim((string)$data[$key]);
            if (empty($data[$key])) {
                return false;
            }
        }
        return $data;
    }
}