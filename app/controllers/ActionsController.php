<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/../models/Comment.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/Logger.php';

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


    public function newTag($tagName){
        $slug = $this->generateSlug($tagName);

        $tagModel = new Tag();

        return $tagModel ->newTag($tagName, $slug);
    }

    private function generateSlug($text)
    {
        // Enleve les accents
        $text = iconv('UTF-8', 'US-ASCII//TRANSLIT', $text);

        // Met tout en minuscules
        $text = strtolower($text);

        // Remplace tout ce qui n'est pas une lettre ou un chiffre par un tiret
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Enleve les tirets en début et fin de chaîne
        $text = trim($text, '-');

        // Valeur par défaut (unique grace au temps) si la chaine est vide
        if (empty($text)) {
            return 'n-a-' . time();
        }

        return $text;
    }


    public function editDraft() {
        $user = SessionManager::getInstance()->get('user');
        if (!$user) return false;
        $id_user = $user['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $etat = $_POST['etat'] ?? 'brouillon';
            $titre = $_POST['titre'] ?? null;
            $contenu = $_POST['contenu'] ?? null;
            $article_id = $_POST['id_article'];
            $tags = $_POST['tags'] ?? [];

            $slug = $this->generateSlug($titre);

            $articleModel = new Article();

            $newImage = $this->uploadImage();
            $deleteRequested = isset($_POST['delete_image']);

            if ($newImage) {
                $imageToSave = $newImage;
            } elseif ($deleteRequested) {
                $imageToSave = null;
            } else {
                $currentArticle = $articleModel->articleById($article_id);

                $imageToSave = $currentArticle['image_une'] ?? null;
            }

            $permissionModel = new Permission();

            if ($permissionModel->checkPermission($id_user, 'article_creer')) {

                $articleModel->updateArticleTags($article_id, $tags);

                $statutFinal = 'Brouillon';

                if ($etat === 'publie' && $permissionModel->checkPermission($id_user, 'article_publier')) {
                    $statutFinal = 'Publié';
                }

                return $articleModel->editDraft($article_id, $titre, $slug, $contenu, $imageToSave, $statutFinal);
            }

            return false;
        }
    }

    private function uploadImage()
    {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

            $targetDir = __DIR__ . '/../../static/img/';

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileInfo = pathinfo($_FILES['file']['name']);
            $extension = strtolower($fileInfo['extension']);

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                return null;
            }

            $newFileName = 'article_' . uniqid() . '.' . $extension;
            $targetFilePath = $targetDir . $newFileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
                return $newFileName;
            }
        }

        return null;
    }


    public function deleteDraft($article_id){
        $articleModel = new Article();
        return $articleModel->deleteArticle($article_id);
    }


    public function postDraft($article_id){
        $articleModel = new Article();
        return $articleModel->postDraft($article_id);
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
            $imageName = $this->uploadImage();

            $tags = $_POST['tags'] ?? [];

            $slug = $this->generateSlug($titre);

            $statut = ($etat === 'publie') ? 'Publié' : 'Brouillon';

            $permissionModel = new Permission();
            $articleModel = new Article();


            if ($etat === 'publie') {
                if ($permissionModel->checkPermission($id_user, 'article_creer') && $permissionModel->checkPermission($id_user, 'article_publier')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Publié', $imageName, $tags);
                }
                elseif ($permissionModel->checkPermission($id_user, 'article_creer')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Brouillon', $imageName, $tags);
                }
            }
            else {
                if ($permissionModel->checkPermission($id_user, 'article_creer')) {
                    return $articleModel->addArticle($id_user, $titre, $slug, $contenu, 'Brouillon', $imageName, $tags);
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
            Logger::getInstance()->log($success);
        }

        return [$id, $success];
    }

    public function disconnect()
    {
        SessionManager::getInstance()->destroy();
    }
}
