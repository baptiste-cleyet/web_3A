<?php
require_once __DIR__ . '/../controllers/DataBase.php';
require_once __DIR__ . '/../controllers/Logger.php';

class Article
{
    public function lastArticles($nbArticles){
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM `articles`
        WHERE statut = 'Publié'
        ORDER BY date_mise_a_jour DESC
        LIMIT :nbArticles;";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':nbArticles', $nbArticles, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);    
    }

    public function articleById($id){
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM `articles`
        WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);    
    }

    public function articleAuthor($id) {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT nom_utilisateur
        FROM utilisateurs
        JOIN articles ON articles.utilisateur_id = utilisateurs.id
        WHERE articles.id = :id;";
        
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    public function articleTags($id){
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT nom_tag FROM tags
        JOIN article_tag ON article_tag.tag_id = tags.id
        JOIN articles ON articles.id = article_tag.article_id
        WHERE articles.id = :id;";
        
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }
}