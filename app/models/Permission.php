<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';

class Permission
{
    public function checkPermission($id, $nom_permission)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = 'SELECT * FROM permissions
        JOIN role_permission ON role_permission.permission_id = permissions.id
        JOIN role_user ON role_user.role_id = role_permission.role_id
        WHERE role_user.user_id = :id AND permissions.nom_permission = :nom_permission;';

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nom_permission', $nom_permission, PDO::PARAM_INT);

        $stmt->execute();

        return count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }
}
