<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/../models/Permission.php';
require_once __DIR__.'/../models/Tag.php';
class ArticlesListController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($tag)
    {
        $articlesModel = new Article();
        $tagsModel = new Tag();
        $tags = $tagsModel->getAllTags();
        $tags = array_merge([['id' => null, 'nom_tag' => 'Tous']], $tags);

        if ($tag !== null) {
            $articlesList = $tagsModel->articlesByTag($tag);
        } else {
            $articlesList = $articlesModel->lastArticles(9);
        }

        echo $this->twig->render('articlesList.twig', [
            'titre_doc' => 'Articles récents',
            'currentPage' => 'articlesList.twig',
            'articlesList' => $articlesList,
            'tags' => $tags,
        ]);
    }
}
