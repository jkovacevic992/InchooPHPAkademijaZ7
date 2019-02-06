<?php

class AdminController
{
    public function login()
    {
    

        $view = new View();    
        $view->render('login', [
            "message" => ""
        ]);
    }

    public function registration()
    {
        $view = new View();
        $view->render('registration',["message"=>""]);
       
    }

    public function register()
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into user (firstname,lastname,email,pass) values (:firstname,:lastname,:email,:pass)");
        $statement->bindValue('firstname', Request::post("firstname"));
        $statement->bindValue('lastname', Request::post("lastname"));
        $statement->bindValue('email', Request::post("email"));
        $statement->bindValue('pass', password_hash(Request::post("pass"),PASSWORD_DEFAULT));
        $statement->execute();

        Session::getInstance()->logout();
        $view = new View();
        $view->render('login',["message"=>""]);
       
    }
    public function change()
    {
        $view = new View();
        $view->render('change_info',["message"=>""]);
    }
    public function changeUserInfo($id)
    {
        try{
            $db = Db::connect();
            $statement = $db->prepare("update user set firstname = :firstname, lastname = :lastname, email = :email, pass = :pass where id=:id");
            $statement->bindValue('firstname', Request::post("firstname"));
            $statement->bindValue('lastname', Request::post("lastname"));
            $statement->bindValue('email', Request::post("email"));
            $statement->bindValue('id',$id);
            $statement->bindValue('pass', password_hash(Request::post("pass"),PASSWORD_DEFAULT));
            $statement->execute();
            Session::getInstance()->logout();
            $view = new View();
            $view->render('login',["message"=>""]);
        }catch (PDOException $exception){
            $view = new View();
            $view->render('change_info',["message"=>"Korisnik s istom email adresom već postoji."]);
        }


    }

    public function delete($post)
    {

        $db = Db::connect();
        $db->beginTransaction();
        $statement = $db->prepare("delete from comment where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from likes where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from post where id=:post");
        $statement->bindValue('post', $post);
    
        $statement->execute();

        $db->commit();
        
        $this->index();
       
    }

    public function comment($post)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into comment (post,user, content) values (:post,:user,:content)");
        $statement->bindValue('post', $post);
        $statement->bindValue('user', Session::getInstance()->getUser()->id);
        $statement->bindValue('content', Request::post("content"));
        $statement->execute();
        
        $view = new View();

        $view->render('view', [
            "post" => Post::find($post)
        ]);
       
    }


    public function like($post)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into likes (post,user) values (:post,:user)");
        $statement->bindValue('post', $post);
        $statement->bindValue('user', Session::getInstance()->getUser()->id);
        $statement->execute();
        
        $this->index();
       
    }


    public function authorize()
    {
//nedostaju kontrole
        $db = Db::connect();
        $statement = $db->prepare("select id, concat(firstname, ' ', lastname) as name, pass from user where email=:email");
        $statement->bindValue('email', Request::post("email"));
        $statement->execute();


        if($statement->rowCount()>0){
            $user = $statement->fetch();
            if(password_verify(Request::post("password"), $user->pass)){
              
                unset($user->pass);
                
                Session::getInstance()->login($user);

                $this->index();
            }else{
                $view = new View();
                $view->render('login',["message"=>"Neispravna kombinacija korisničko ime i lozinka"]);
            }
        }else{
            $view = new View();
            $view->render('login',["message"=>"Neispravan email"]);
        }



       
    }

    public function logout()
    {
    
        Session::getInstance()->logout();
        $this->index();
    }

    //JSON EXAMPLE FOR THE LAST TASK
//    public function json()
//    {
//
//        $posts = Post::all();
//       //print_r($posts);
//        echo json_encode($posts);
//    }

    public function index()
    {

        $posts = Post::all();
        $view = new View();
        $view->render('index', [
            "posts" => $posts
        ]);
    }



   
}