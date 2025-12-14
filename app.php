<?php 

/* récupérer le tableau des données */ 
// require 'models/data.php'; 

 /* inclure l'autoloader */ 
require_once 'vendor/autoload.php';

/* templates chargés à partir du système de fichiers (répertoire vue) */ 
$loader = new Twig\Loader\FilesystemLoader('app/views');

$twig = new \Twig\Environment($loader, ['cache' => false]);

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

    case 'home':
    default:
        echo $twig->render('signup.twig');
        break;
}

/* options : prod = cache dans le répertoire cache, dev = pas de cache */ 
$options_prod = array('cache' => 'cache', 'autoescape' => true); 
$options_dev = array('cache' => false, 'autoescape' => true); 

/* stocker la configuration */ 
$twig = new Twig\Environment($loader); 

/* charger+compiler le template, exécuter, envoyer le résultat au navigateur */ 
//echo $twig->render('signup.twig');

