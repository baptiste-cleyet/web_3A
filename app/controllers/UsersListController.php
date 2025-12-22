<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
class UsersListController
{
    private $twig;

    // Constructeur
    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        $roleModel = new Role();

        $usersList = $roleModel->users_list_with_roles();
        $rolesList = $roleModel->roles_list();

        echo $this->twig->render('usersList.twig', [
            'usersList' => $usersList,
            'titre_doc' => 'Gestion des utilisateurs',
            'rolesList' => $rolesList,
        ]);
    }
}
