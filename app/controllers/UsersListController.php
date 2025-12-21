<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/User.php';
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
        $userModel = new User();
        $usersList = $userModel->users_list_with_roles();

        echo $this->twig->render('usersList.twig', [
            'usersList' => $usersList,
            'titre_doc' => 'Gestion des utilisateurs',
        ]);
    }
}
