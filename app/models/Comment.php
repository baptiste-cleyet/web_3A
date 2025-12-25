<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';

class Comment
{

    public function allComments(){
        $pdo = DataBase::getInstance()->getConnection();

        $sql = "SELECT * FROM Commentaires
        WHERE statut = 'En attente'";

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function search_comments_with_user_or_content($query)
    {
        $pdo = DataBase::getInstance()->getConnection();

        $sql = "SELECT * 
        FROM Commentaires 
        WHERE statut = 'En attente'
        AND (nom_auteur LIKE :query
        OR email_auteur LIKE :query
        OR contenu LIKE :query)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([':query' => "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function reject_comment($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE Commentaires SET statut = 'Rejeté' WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            Logger::getInstance()->log("Succès : Commentaire rejeté ($id)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR SUPRESSION de $id - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }


    public function approve_comment($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE Commentaires SET statut = 'Approuvé' WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            Logger::getInstance()->log("Succès : Commentaire approuvé ($id)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR SUPRESSION de $id - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }
}
