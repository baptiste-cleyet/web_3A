<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/Controller.php';
class UsersListController extends Controller
{
    // Constructeur
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $roleModel = new Role();
        $userModel = new User();

        $search = $_GET['search'] ?? null;

        if ($search) { // si on a recherché quelque chose
            $usersList = $userModel->search_users_with_roles($search);
        } else { // pas de recherceh
            $usersList = $roleModel->users_list_with_roles();
        }

        $rolesList = $roleModel->roles_list();

        echo $this->twig->render('usersList.twig', [
            'currentPage' => 'usersList.twig',
            'titre_doc' => 'Gestion des utilisateurs',
            'usersList' => $usersList,
            'rolesList' => $rolesList,
            'currentSearch' => $search,
        ]);
    }
}
