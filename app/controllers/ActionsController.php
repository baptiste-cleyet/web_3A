<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/../models/Comment.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/../models/Article.php';

class ActionsController extends Controller
{
    public function deleteUser($id)
    {
        parent::__construct();

        $permissionModel = new Permission();
        if ($permissionModel->checkPermission($id, 'utilisateur_gerer')) {
            $userModel = new User();

            return $userModel->delete_user($id);
        }

        return;
    }

    public function archiveArticle($id)
    {
        parent::__construct();

        $articleModel = new Article();

        return $articleModel->archive_article($id);
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

        if ($success) {
            $nb = $articleModel->countWaitingComment() ?? null;
            if ($nb) {
                $nbPhrase = "\r\nIl y a désormais $nb commentaires en attente de validation.";
            } else {
                $nbPhrase = "\r\nInformation sur le nombre total de commentaires en attente indisponible.";
            }

            // Le message
            $message = "Bonjour,\r\nUn nouveau commentaire a été ajouté en attente.".$nbPhrase;

            // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap()
            $message = wordwrap($message, 70, "\r\n");

            // Envoi du mail
            $success = $this->sendEmail('Commentaire en attente de validation', $message, $this->commentNotificationEmail);
        }

        return [$id, $success];
    }

    public function disconnect()
    {
        SessionManager::getInstance()->destroy();
    }
}
