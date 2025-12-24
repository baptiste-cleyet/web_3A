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
        } else {
            $permissionModel = new Permission();
            $userGestion = $permissionModel->checkPermission($user_id, 'utilisateur_gerer');
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
        ];
        $this->twig->addGlobal('menu', $menu);
    }
}
