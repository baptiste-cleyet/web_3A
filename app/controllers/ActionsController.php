<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/../models/Comment.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/../models/Article.php';

class ActionsController
{
    public function deleteUser($id)
    {
        $permissionModel = new Permission();
        if ($permissionModel->checkPermission($id, 'utilisateur_gerer')) {
            $userModel = new User();

            return $userModel->delete_user($id);
        }

        return;
    }

    public function rejectComment($id_comment, $id_user)
    {
        $permissionModel = new Permission();

        if ($permissionModel->checkPermission($id_user, 'commentaire_gerer')) {
            $commentModel = new Comment();

            return $commentModel->reject_comment($id_comment);
        }

        return;
    }

    public function approveComment($id_comment, $id_user)
    {
        $permissionModel = new Permission();
        if ($permissionModel->checkPermission($id_user, 'commentaire_gerer')) {

            $commentModel = new Comment();

            return $commentModel->approve_comment($id_comment);
        }

        return;
    }
    public function updateRoles()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $contributeur = $_POST['Contributeur'] ?? false;
            $administrateur = $_POST['Administrateur'] ?? false;
            $editeur = $_POST['Éditeur'] ?? false;

            $roleModel = new Role();

            $roleModel->update_roles($id, $administrateur, $contributeur, $editeur);
        }
    }

    public function addComment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $pseudo = $_POST['pseudo'] ?? '';
            $mail = $_POST['mail'] ?? null;
            $commentContent = $_POST['commentContent'] ?? '';

            $articleModel = new Article();

            $success = $articleModel->addComment($id, $mail, $pseudo, $commentContent);
        }

        return [$id, $success];
    }

    public function disconnect()
    {
        SessionManager::getInstance()->destroy();
    }
}
