<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';

class User
{
    public function register($username, $email, $password)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (:nom_utilisateur, :email, :mot_de_passe)';
        $stmt = $pdo->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt->execute([
                ':nom_utilisateur' => $username,
                ':email' => $email,
                ':mot_de_passe' => $hashedPassword,
            ]);
            Logger::getInstance()->log("Succès : Nouvel utilisateur inscrit ($username)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR INSCRIPTION - username: $username - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }

    public function getId($username)
    {
        $pdo = DataBase::getInstance()->getConnection();

        $sql = 'SELECT id FROM Utilisateurs WHERE nom_utilisateur = :username';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function find_by_email($email)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT * FROM Utilisateurs WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verify_password($email, $password)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT mot_de_passe FROM Utilisateurs WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $hashedPassword = $stmt->fetch(PDO::FETCH_ASSOC)['mot_de_passe'];

        if (password_verify($password, $hashedPassword)) {
            return true;
        }

        return false;
    }

    public function delete_user($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'DELETE FROM utilisateurs
        WHERE id = :id;';

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            Logger::getInstance()->log("Succès : Utilisateur supprimé ($id)");

            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR SUPRESSION de $id - Raison SQL: ".$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }
}
