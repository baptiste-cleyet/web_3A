<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/Controller.php';
class ArticlePage extends Controller
{
    protected $twig;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->twig = $twig;
    }

    public function index($id, $error, $addComment)
    {
        $articlesModel = new Article();

        $article = $articlesModel->articleById($id);
        $author = $articlesModel->articleAuthor($id);
        $tags = $articlesModel->articleTags($id);
        $comments = $articlesModel->articleComments($id);

        $user = SessionManager::getInstance()->get('user');

        echo $this->twig->render('articlePage.twig', [
            'titre_doc' => $article['titre'],
            'currentPage' => 'articlePage.twig',
            'article' => $article,
            'auteur' => $author,
            'tags' => $tags,
            'comments' => $comments,
            'error' => $error,
            'addComment' => $addComment,
        ]);
    }
}
