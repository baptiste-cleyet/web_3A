<?php

require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/Controller.php';
class LoginController extends Controller
{
    protected $twig;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->twig = $twig;
    }

    public function index()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';

            $userModel = new User();
            $roleModel = new Role();

            if ($email && $password) {
                $user = $userModel->find_by_email($email);

                if ($user) {
                    if ($userModel->verify_password($email, $password)) {
                        $roles = $roleModel->user_roles($user['id']);

                        SessionManager::getInstance()->set('roles', $roles);
                        SessionManager::getInstance()->set('user', $user);

                        header('Location: app.php?route=articlesList');
                        exit;
                    } else {
                        $error = 'Mot de passe incorrect';
                    }
                } else {
                    $error = "Le compte n'existe pas";
                }
            } else {
                $error = 'Veuillez renseigner tous les champs.';
            }
        }
        echo $this->twig->render('login.twig', [
            'titre_doc' => 'Connexion',
            'currentPage' => 'login.twig',
            'error' => $error,
        ]);
    }
}
