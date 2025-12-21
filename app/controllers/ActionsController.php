<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/User.php';

class ActionsController
{
    public function deleteUser($id)
    {
        $roles = SessionManager::getInstance()->get('roles');
        $admin = false;
        for ($i = 0; $i < count($roles); ++$i) {
            if ($roles[$i]['nom_role'] == 'Administrateur') {
                $admin = true;
                break;
            }
        }
        if (!$admin) {
            return;
        }
        $userModel = new User();

        return $userModel->delete_user($id);
    }
}
