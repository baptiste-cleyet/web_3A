<?php

use Twig\Extra\Markdown\ErusevMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/* inclure l'autoloader */
require_once 'vendor/autoload.php';

/* templates chargés à partir du système de fichiers (répertoire vue) */
$loader = new Twig\Loader\FilesystemLoader('app/views');

$twig = new Twig\Environment($loader, ['cache' => false]);

$twig->addExtension(new MarkdownExtension());

$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    MarkdownRuntime::class => function () {
        return new MarkdownRuntime(new ErusevMarkdown());
    },
]));

$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'signup':
        require_once 'app/controllers/RegisterController.php';
        (new RegisterController($twig))->index();
        break;

    case 'login':
        require_once 'app/controllers/LoginController.php';
        (new LoginController($twig))->index();
        break;

    case 'articlesList':
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController($twig))->index();
        break;

    case 'article':
        $id = $_GET['id'];
        $error = $_GET['commentError'] ?? false;
        $addComment = $_GET['addComment'] ?? false;

        require_once 'app/controllers/ArticlePageController.php';
        (new ArticlePage($twig))->index($id, $error, $addComment);
        break;

    case 'usersList' :
        require_once 'app/controllers/UsersListController.php';
        (new UsersListController($twig))->index();
        break;

    case 'home':
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController($twig))->index();
        break;

    default:
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController($twig))->index();
        break;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'delete':
        require_once 'app/controllers/ActionsController.php';
        $delete_id = $_GET['delete_id'];
        (new ActionsController())->deleteUser($delete_id);
        header('Location: app.php?route=usersList');
        break;

    case 'updateRoles':
        require_once 'app/controllers/ActionsController.php';
        (new ActionsController())->updateRoles();
        header('Location: app.php?route=usersList');
        break;

    case 'addComment' :
        require_once 'app/controllers/ActionsController.php';
        $var = (new ActionsController())->addComment();
        $id = $var[0];
        $error = !$var[1];
        header("Location: app.php?route=article&id=$id&commentError=$error&addComment=true");
        break;

    case 'disconnect' :
        require_once 'app/controllers/ActionsController.php';
        (new ActionsController())->disconnect();
        header('Location: app.php?route=articlesList');
        break;

    default:
        break;
}

/* options : prod = cache dans le répertoire cache, dev = pas de cache */
$options_prod = ['cache' => 'cache', 'autoescape' => true];
$options_dev = ['cache' => false, 'autoescape' => true];

/* stocker la configuration */
$twig = new Twig\Environment($loader);
