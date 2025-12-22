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

    public function getId($username){
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

    public function roles($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT nom_role FROM roles
        JOIN role_user ON role_user.role_id = roles.id
        JOIN utilisateurs ON utilisateurs.id = role_user.user_id
        WHERE utilisateurs.id = :id;';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function users_list_with_roles()
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT roles.nom_role, utilisateurs.id, utilisateurs.nom_utilisateur, utilisateurs.email FROM roles
        JOIN role_user ON role_user.role_id = roles.id
        JOIN utilisateurs ON utilisateurs.id = role_user.user_id
        ORDER BY utilisateurs.id;';

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grouped_res = []; // groupe les rôles par utilisateur

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

    public function roles_list()
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT nom_role FROM roles;';

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_roles($id, $admin, $contrib, $edit)
    {
        $this->delete_all_roles($id);
        if ($admin) {
            $this->add_role($id, 1);
        }
        if ($edit) {
            $this->add_role($id, 2);
        }
        if ($contrib) {
            $this->add_role($id, 3);
        }
    }

    private function add_role($user_id, $role_id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'INSERT INTO role_user (user_id, role_id) VALUES(:user_id, :role_id)';

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);

            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            $errorMessage = 'ERREUR ajout de rôle - Raison SQL: '.$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }

    private function delete_all_roles($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'DELETE FROM role_user
        WHERE user_id = :id;';

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            $errorMessage = 'ERREUR supression de rôle - Raison SQL: '.$e->getMessage();
            Logger::getInstance()->log($errorMessage);

            return false;
        }
    }
}
