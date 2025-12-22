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

    public function updateRoles()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $contributeur = $_POST['Contributeur'] ?? false;
            $administrateur = $_POST['Administrateur'] ?? false;
            $editeur = $_POST['Éditeur'] ?? false;

            $userModel = new User();

            $userModel->update_roles($id, $administrateur, $contributeur, $editeur);
        }
    }
}
