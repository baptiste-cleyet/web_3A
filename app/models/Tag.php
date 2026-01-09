<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';

class Tag
{
    public function getId($tag_name)
    {
        $pdo = DataBase::getInstance()->getConnection();

        $sql = 'SELECT id FROM Tags WHERE nom_tag = :nom_tag';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nom_tag' => $tag_name]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTags(){
        $pdo = DataBase::getInstance()->getConnection();

        $sql = 'SELECT * FROM Tags ORDER BY nom_tag ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function newTag($tag_name, $slug){
        $pdo = DataBase::getInstance()->getConnection();



        $sql = 'INSERT INTO Tags (nom_tag, slug)
        VALUES (:nom_tag, :slug)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom_tag' => $tag_name,
            ':slug' => $slug,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
