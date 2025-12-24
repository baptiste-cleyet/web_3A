<?php

require_once __DIR__.'/../models/Permission.php';
abstract class Controller
{
    protected $twig;

    public function __construct($twig)
    {
        // On définit ici les données partagées par TOUS les contrôleurs
        $this->twig = $twig;

        $user = SessionManager::getInstance()->get('user');
        $this->twig->addGlobal('user', $user);
        $user_id = $user['id'] ?? null;

        if ($user == null) {
            $userGestion = false;
            $commentGestion = false;
        } else {
            $permissionModel = new Permission();
            $userGestion = $permissionModel->checkPermission($user_id, 'utilisateur_gerer');
            $commentGestion = $permissionModel->checkPermission($user_id, 'commentaire_gerer');
        }
        $menu = [
            'articlesList' => [
                'url' => 'app.php?route=articlesList',
                'label' => 'Accueil',
                'disabled' => false,
                'page' => 'articlesList.twig',
            ],
            'gestionUtilisateurs' => [
                'url' => 'app.php?route=usersList',
                'label' => 'Gestion utilisateurs',
                'disabled' => !$userGestion,
                'page' => 'usersList.twig',
            ],
            'gestionCommentaires' => [
                'url' => 'app.php?route=manageComments',
                'label' => 'Gestion commentaires',
                'disabled' => !$commentGestion,
                'page' => 'manageComments.twig',
            ],
        ];
        $this->twig->addGlobal('menu', $menu);
    }
}
