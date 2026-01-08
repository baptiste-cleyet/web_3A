<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';

class Article
{
    public function lastArticles($nbArticles)
    {
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

    public function articleById($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT * FROM `articles`
        WHERE id = :id';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function articleAuthor($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT nom_utilisateur
        FROM utilisateurs
        JOIN articles ON articles.utilisateur_id = utilisateurs.id
        WHERE articles.id = :id;';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getArticlesByAuthor($id_auteur)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT * FROM articles
        WHERE utilisateur_id = :id_auteur';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id_auteur', $id_auteur, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function articleTags($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT nom_tag FROM tags
        JOIN article_tag ON article_tag.tag_id = tags.id
        JOIN articles ON articles.id = article_tag.article_id
        WHERE articles.id = :id;';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function articleComments($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT commentaires.contenu, commentaires.date_commentaire, commentaires.nom_auteur
        FROM `commentaires`
        JOIN articles ON articles.id = commentaires.article_id
        WHERE articles.id = :id AND commentaires.statut = 'Approuvé';";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addComment($article_id, $email, $nom, $contenu)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'INSERT INTO commentaires (article_id, contenu, email_auteur, nom_auteur) 
        VALUES (:article_id, :contenu, :email, :nom);';

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':article_id' => $article_id,
                ':contenu' => $contenu,
                ':email' => $email,
                ':nom' => $nom,
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function archive_article($id): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE Articles SET statut = 'Archivé' WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            Logger::getInstance()->log("Succès : Article archivé ($id)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR SUPRESSION de $id - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }

    public function restore_article($id): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE Articles SET statut = 'Publié' WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            Logger::getInstance()->log("Succès : Article restauré ($id)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR SUPRESSION de $id - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }


    public function addArticle($user_id, $titre, $slug, $contenu, $image_name, $statut) : bool
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "INSERT INTO Articles(utilisateur_id, titre, slug, contenu, image_une, statut, date_creation) 
            VALUES(:utilisateur_id, :titre, :slug, :contenu, :image, :statut, NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':utilisateur_id' => $user_id,
                ':titre' => $titre,
                ':slug' => $slug,
                ':contenu' => $contenu,
                ':image' => $image_name,
                ':statut' => $statut,
            ]);
            return true;
        } catch (PDOException $e) {
            die("ERREUR SQL : " . $e->getMessage());
        }
    }

    public function editDraft($id, $titre, $slug, $contenu, $image, $statut)
    {
        $pdo = Database::getInstance()->getConnection();

        // Note : On met à jour 'image' directement avec la valeur reçue
        $sql = "UPDATE articles SET 
            titre = :titre, 
            slug = :slug, 
            contenu = :contenu, 
            image_une = :image, 
            statut = :statut, 
            date_mise_a_jour = NOW() 
            WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':id' => $id,
                ':titre' => $titre,
                ':slug' => $slug,
                ':contenu' => $contenu,
                ':image' => $image,
                ':statut' => $statut
            ]);
            return true;
        } catch (PDOException $e) {
            Logger::getInstance()->log("Erreur SQL article : ($id) " . $e);
            return false;
        }
    }


    public function getAllArticles(){
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM Articles;";

        $stmt = $pdo->prepare($sql);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function deleteArticle($article_id){
        $pdo = Database::getInstance()->getConnection();

        $sql = "DELETE FROM Articles WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':id' => $article_id,
            ]);
            return true;
        } catch (PDOException $e) {
            die("ERREUR SQL : " . $e->getMessage());
        }
    }


    public function postDraft($article_id){
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE Articles SET statut = 'Publié'
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':id' => $article_id,
            ]);
            return true;
        } catch (PDOException $e) {
            die("ERREUR SQL : " . $e->getMessage());
        }
    }

    public function countWaitingComment()
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT COUNT(*) as nb FROM commentaires
            WHERE statut = 'En attente';";

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
