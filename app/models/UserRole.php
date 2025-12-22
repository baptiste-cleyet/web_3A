<?php

require_once __DIR__.'/../controllers/DataBase.php';
require_once __DIR__.'/../controllers/Logger.php';
require_once __DIR__.'/../controllers/SessionManager.php';
require_once __DIR__.'/../models/User.php';
class UserRole
{
    public function setContributor($username){
        $userModel = new User();
        $id = $userModel->getId($username)['id'];

        $pdo = Database::getInstance()->getConnection();
        $sql = 'INSERT INTO role_user (role_id, user_id) VALUES (3, :id_user)';
        $stmt = $pdo->prepare($sql);

        $stmt->execute([':id_user' => $id]);
    }
}