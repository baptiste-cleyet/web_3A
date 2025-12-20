<?php

require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../models/Article.php';
class ArticlesListController
{
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index() {
        $session = SessionManager::getInstance();
        $username = $session->get('username');
        $role = $session->get('role');

        $articlesModel = new Article();
        $articlesList = $articlesModel->lastArticles(9);


        echo $this->twig->render('articlesList.twig', [
            'username' => $username,
            'role' => $role,
            'articlesList' => $articlesList,
            'titre_doc' => "Articles récents"
        ]);
    }
}
