<?php
require_once __DIR__ . '/../controllers/DataBase.php';
require_once __DIR__ . '/../controllers/Logger.php';
class User
{
    public function register($email, $username, $password){
        $pdo = Database::getInstance()->getConnection();

        $sql = "INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (:nom_utilisateur, :email, :mot_de_passe)";
        $stmt = $pdo->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt->execute([
                ':nom_utilisateur' => $username,
                ':email' => $email,
                ':mot_de_passe' => $hashedPassword
            ]);
            Logger::getInstance()->log("Succès : Nouvel utilisateur inscrit ($username)");
            return true;
        } catch (PDOException $e) {
            $errorMessage = "ERREUR INSCRIPTION - username: $username - Raison SQL: " . $e->getMessage();
            Logger::getInstance()->log($errorMessage);
            return false;
        }
    }
}