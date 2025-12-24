<?php

require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/UserRole.php';
require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/Controller.php';

class RegisterController extends Controller
{
    protected $twig;

    // Constructeur
    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->twig = $twig;
    }

    // La méthode principale qui gère la page
    public function index()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyage des entrées
            $nom = htmlspecialchars($_POST['username'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password1 = $_POST['password1'] ?? '';
            $password2 = $_POST['password2'] ?? '';

            if ($nom && $email && $password1 && $password2) {
                if ($password1 === $password2) {
                    $userModel = new User();

                    if ($userModel->register($nom, $email, $password1)) {
                        $userRoleModel = new UserRole();
                        $userRoleModel->setContributor($nom);
                        $session = SessionManager::getInstance();
                        $session->set('user_name', $nom);
                        header('Location: app.php?route=login&success=1');
                        exit;
                    } else {
                        $error = "L'email ou le nom d'utilisateur est déjà pris.";
                    }
                } else {
                    $error = 'Les mots de passe ne correspondent pas.';
                }
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        echo $this->twig->render('signup.twig', [
            'error' => $error,
        ]);
    }
}
