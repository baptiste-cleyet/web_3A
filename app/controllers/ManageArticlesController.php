<?php
require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/Controller.php';
class ManageArticlesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = SessionManager::getInstance()->get('user');
        $user_id = $user['id'];

        $articleModel = new Article();

        $publishedArticles = $articleModel->getPublishedArticlesByAuthor($user_id);

        echo $this->twig->render('ManageArticles.twig', [
            'currentPage' => 'ManageArticles.twig',
            'publishedArticles' => $publishedArticles,
            'titre_doc' => 'Gestion des articles',
        ]);
    }
}