<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';

class Role
{
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

    public function user_roles($id)
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
}
