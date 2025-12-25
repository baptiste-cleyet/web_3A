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



    public function search_users_with_roles($query)
    {
        $pdo = Database::getInstance()->getConnection();


        $sql = 'SELECT roles.nom_role, utilisateurs.id, utilisateurs.nom_utilisateur, utilisateurs.email 
        FROM roles
        JOIN role_user ON role_user.role_id = roles.id
        JOIN utilisateurs ON utilisateurs.id = role_user.user_id
        WHERE utilisateurs.nom_utilisateur LIKE :query OR utilisateurs.email LIKE :query
        ORDER BY utilisateurs.id;';

        $stmt = $pdo->prepare($sql);


        $stmt->execute([':query' => "%$query%"]);

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $grouped_res = [];
        $previous_id = -1;
        for ($i = 0; $i < count($res); ++$i) {
            if ($res[$i]['id'] == $previous_id) {
                array_push($grouped_res[count($grouped_res) - 1]['nom_roles'], $res[$i]['nom_role']);
            } else {
                $new_array = $res[$i];
                unset($new_array['nom_role']);
                $new_array['nom_roles'] = [$res[$i]['nom_role']];
                array_push($grouped_res, $new_array);
            }
            $previous_id = $res[$i]['id'];
        }

        return $grouped_res;
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
