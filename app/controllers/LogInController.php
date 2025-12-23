<?php

require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../models/Role.php';
require_once __DIR__.'/SessionManager.php';
class LoginController
{
    private $twig;

    public function __construct($twig)
    {
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

                        SessionManager::getInstance()->set('username', $user['nom_utilisateur']);
                        SessionManager::getInstance()->set('roles', $roles);
                        SessionManager::getInstance()->set('user_id', $user['id']);
                        SessionManager::getInstance()->set('user', $user);

                        header('Location: app.php?route=articlesList');
                        exit;
                    } else {
                        $error = 'mot de passe incorrect';
                    }
                } else {
                    $error = "Le compte n'existe pas";
                }
            } else {
                $error = 'Veuillez renseigner tous les champs.';
            }
        }
        echo $this->twig->render('login.twig', [
            'error' => $error,
        ]);
    }
}
