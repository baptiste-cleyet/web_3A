<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/Controller.php';
class ArticlesListController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $articlesModel = new Article();
        $articlesList = $articlesModel->lastArticles(9);

        echo $this->twig->render('articlesList.twig', [
            'titre_doc' => 'Articles récents',
            'currentPage' => 'articlesList.twig',
            'articlesList' => $articlesList,
        ]);
    }
}
