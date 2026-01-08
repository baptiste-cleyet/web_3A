<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/Permission.php';
class ManageArticlesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = SessionManager::getInstance()->get('user');
        $user_id = $user['id'] ?? null;

        $permissionModel = new Permission();


        $articleModel = new Article();
        $articlesList = $articleModel->getArticlesByAuthor($user_id);



        echo $this->twig->render('manageArticles.twig', [
            'currentPage' => 'manageArticles.twig',
            'articlesList' => $articlesList,
            'user_id' => $user_id,
            'titre_doc' => 'Gestion des articles',
        ]);
    }
}
