<?php

require_once __DIR__ . '/SessionManager.php';
class articlesListController
{
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index() {
        $session = SessionManager::getInstance();
        $username = $session->get('user_name');


        echo $this->twig->render('articlesList.twig', [
            'user_name' => $username
        ]);
    }
}
