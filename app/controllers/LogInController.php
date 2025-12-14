<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/SessionManager.php';
class LoginController {
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index() {
        // Affiche simplement la vue login.twig
        echo $this->twig->render('login.twig');
    }
}