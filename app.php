<?php 

use Twig\Extra\Markdown\ErusevMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

 /* inclure l'autoloader */ 
require_once 'vendor/autoload.php';


/* templates chargés à partir du système de fichiers (répertoire vue) */ 
$loader = new Twig\Loader\FilesystemLoader('app/views');

$twig = new \Twig\Environment($loader, ['cache' => false]);

$twig->addExtension(new MarkdownExtension());

$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    MarkdownRuntime::class => function() {
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

        require_once 'app/controllers/ArticlePage.php';
        (new ArticlePage($twig))->index($id);
        break;

    case 'usersList' :
        require_once 'app/controllers/UsersListController.php';
        (new UsersListController($twig))->index();
        break;

    case 'home':
    
    default:
        echo $twig->render('login.twig');
        break;
}

/* options : prod = cache dans le répertoire cache, dev = pas de cache */ 
$options_prod = array('cache' => 'cache', 'autoescape' => true); 
$options_dev = array('cache' => false, 'autoescape' => true); 

/* stocker la configuration */ 
$twig = new Twig\Environment($loader); 

