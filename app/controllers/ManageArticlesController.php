<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/../models/Tag.php';
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

        $articleModel = new Article();
        $tagModel = new Tag();

        $articlesList = $articleModel->getArticlesByAuthor($user_id);

        $tagsList = $tagModel->getAllTags();

        foreach ($articlesList as &$article) {

            $mesTags = $articleModel->articleTags($article['id']);


            $article['tags_ids'] = array_column($mesTags, 'id');
        }
        // ------------------------

        echo $this->twig->render('manageArticles.twig', [
            'currentPage' => 'manageArticles.twig',
            'articlesList' => $articlesList,
            'tagsList' => $tagsList,
            'user_id' => $user_id,
            'titre_doc' => 'Gestion des articles',
        ]);
    }
}
