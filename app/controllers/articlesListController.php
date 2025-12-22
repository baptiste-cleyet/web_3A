<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/../models/Permission.php';
class ArticlesListController
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        $session = SessionManager::getInstance();
        $username = $session->get('username');
        $roles = $session->get('roles');
        $id = $session->get('user_id');

        $articlesModel = new Article();
        $articlesList = $articlesModel->lastArticles(9);

        $permissionModel = new Permission();
        $userGestion = $permissionModel->checkPermission($id, 'utilisateur_gerer');

        echo $this->twig->render('articlesList.twig', [
            'username' => $username,
            'roles' => $roles,
            'userGestion' => $userGestion,
            'articlesList' => $articlesList,
            'titre_doc' => 'Articles récents',
        ]);
    }
}
