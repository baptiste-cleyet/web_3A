<?php

require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../models/Article.php';
class ArticlePage
{
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index($id) {

        $articlesModel = new Article();
        $article = $articlesModel->articleById($id);

        $author = $articlesModel->articleAuthor($id);

        $tags = $articlesModel->articleTags($id);


        echo $this->twig->render('articlePage.twig', [
            'titre_doc' => $article["titre"],
            "article" => $article,
            "auteur" => $author,
            'tags' => $tags
        ]);
    }
}