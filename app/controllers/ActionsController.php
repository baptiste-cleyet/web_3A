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

    public function restoreArticle($id)
    {
        parent::__construct();

        $articleModel = new Article();

        return $articleModel->restore_article($id);
    }


    private function generateSlug($text)
    {
        // Enleve les accents
        $text = iconv('UTF-8', 'US-ASCII//TRANSLIT', $text);

        // Met tout en minuscules
        $text = strtolower($text);

        // Remplace tout ce qui n'est pas une lettre ou un chiffre par un tiret
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Enleve les tirets en début et fin de chaîne (trim)
        $text = trim($text, '-');

        // Valeur par défaut (unique grace au temps) si la chaine est vide
        if (empty($text)) {
            return 'n-a-' . time();
        }

        return $text;
    }

    public function newArticle()
    {
        $user = SessionManager::getInstance()->get('user');
        if (!$user) {
            return false;
        }
        $id_user = $user['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $etat = $_POST['etat'] ?? 'brouillon';
            $titre = $_POST['titre'] ?? null;
            $contenu = $_POST['contenu'] ?? null;

            $slug = $this->generateSlug($titre);
            $statut = ($etat === 'publie') ? 'Publié' : 'Brouillon';

            $permissionModel = new Permission();
            $articleModel = new Article();

            if ($etat === 'publie') {
                if ($permissionModel->checkPermission($id_user, 'article_creer') && $permissionModel->checkPermission($id_user, 'article_publier')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Publié');
                }
                elseif ($permissionModel->checkPermission($id_user, 'article_creer')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Brouillon');
                }
            }
            else {
                if ($permissionModel->checkPermission($id_user, 'article_creer')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Brouillon');
                }
            }
            return false;
        }
    }

    public function rejectComment($id_comment, $id_user)
    {
        $permissionModel = new Permission();

        if ($permissionModel->checkPermission($id_user, 'commentaire_gerer')) {
            $commentModel = new Comment();

            return $commentModel->reject_comment($id_comment);
        }
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
            $nb = $articleModel->countWaitingComment()['nb'] ?? null;
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
