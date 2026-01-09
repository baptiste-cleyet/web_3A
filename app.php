<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* inclure l'autoloader */
require_once 'vendor/autoload.php';

require_once 'app/controllers/SessionManager.php';
require_once 'app/models/Permission.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ------------------------------------- Router -----------------------------------------

$action = $_GET['action'] ?? '';

if (!empty($action)) {
    switch ($action) {
        case 'delete':
            require_once 'app/controllers/ActionsController.php';
            $delete_id = $_GET['delete_id'];
            (new ActionsController())->deleteUser($delete_id);
            header('Location: app.php?route=usersList');
            exit;

        case 'updateRoles':
            require_once 'app/controllers/ActionsController.php';
            (new ActionsController())->updateRoles();
            header('Location: app.php?route=usersList');
            exit;

        case 'archiveArticle':
            require_once 'app/controllers/ActionsController.php';
            $archive_id = $_GET['id'];
            (new ActionsController())->archiveArticle($archive_id);
            header('Location: app.php?route=manageArticles');
            exit;

        case 'newArticle':
            require_once 'app/controllers/ActionsController.php';
            (new ActionsController())->newArticle();
            header('Location: app.php?route=manageArticles');
            exit;

        case 'deleteDraft':
            require_once 'app/controllers/ActionsController.php';
            $article_id = $_GET['id'];
            (new ActionsController())->deleteDraft($article_id);
            header('Location: app.php?route=manageArticles');
            exit;

        case 'postDraft':
            require_once 'app/controllers/ActionsController.php';
            $article_id = $_GET['id'];
            (new ActionsController())->postDraft($article_id);
            header('Location: app.php?route=manageArticles');
            exit;

        case 'editDraft':
            require_once 'app/controllers/ActionsController.php';
            (new ActionsController())->editDraft();
            header('Location: app.php?route=manageArticles');
            exit;

        case 'restoreArticle':
            require_once 'app/controllers/ActionsController.php';
            $archive_id = $_GET['id'];
            (new ActionsController())->restoreArticle($archive_id);
            header('Location: app.php?route=manageArticles');
            exit;

        case 'createArticle': // J'ajoute celui qu'on faisait avant
            require_once 'app/controllers/ActionsController.php';
            (new ActionsController())->createArticle();
            header('Location: app.php?route=manageArticles');
            exit;

        case 'addComment' :
            require_once 'app/controllers/ActionsController.php';
            $var = (new ActionsController())->addComment();
            $id = $var[0];
            $error = !$var[1];
            header("Location: app.php?route=article&id=$id&commentError=$error&addComment=true");
            exit;

        case 'rejectComment':
            require_once 'app/controllers/ActionsController.php';
            $comment_id = $_GET['id'];
            $user_id = $_GET['user_id'];
            (new ActionsController())->rejectComment($comment_id, $user_id);
            header('Location: app.php?route=manageComments');
            exit;

        case 'approveComment' :
            require_once 'app/controllers/ActionsController.php';
            $comment_id = $_GET['id'];
            $user_id = $_GET['user_id'];
            (new ActionsController())->approveComment($comment_id, $user_id);
            header('Location: app.php?route=manageComments');
            exit;

        case 'disconnect' :
            require_once 'app/controllers/ActionsController.php';
            (new ActionsController())->disconnect();
            header('Location: app.php?route=articlesList');
            exit;

        default:
            break;
    }
}

$route = $_GET['route'] ?? 'home';
$user = SessionManager::getInstance()->get('user');
$id = $user['id'] ?? null;

switch ($route) {
    case 'signup':
        require_once 'app/controllers/RegisterController.php';
        (new RegisterController())->index();
        break;

    case 'login':
        require_once 'app/controllers/LoginController.php';
        (new LoginController())->index();
        break;

    case 'articlesList':
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController())->index();
        break;

    case 'article':
        $id = $_GET['id'];
        $error = $_GET['commentError'] ?? false;
        $addComment = $_GET['addComment'] ?? false;

        require_once 'app/controllers/ArticlePageController.php';
        (new ArticlePage())->index($id, $error, $addComment);
        break;

    case 'usersList' :
        if ($id === null || !(new Permission())->checkPermission($id, 'utilisateur_gerer')) {
            header('Location: app.php?route=home');
            exit;
        }
        require_once 'app/controllers/UsersListController.php';
        (new UsersListController())->index();
        break;

    case 'manageComments' :
        if ($id === null || !(new Permission())->checkPermission($id, 'commentaire_gerer')) {
            header('Location: app.php?route=home');
            exit;
        }
        require_once 'app/controllers/ManageCommentController.php';
        (new ManageCommentController())->index();
        break;

    case 'manageArticles' :
        require_once 'app/controllers/ManageArticlesController.php';
        (new ManageArticlesController())->index();
        break;

    case 'home':
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController())->index();
        break;

    default:
        require_once 'app/controllers/ArticlesListController.php';
        (new ArticlesListController())->index();
        break;
}

/* options : prod = cache dans le répertoire cache, dev = pas de cache */
$options_prod = ['cache' => 'cache', 'autoescape' => true];
$options_dev = ['cache' => false, 'autoescape' => true];
