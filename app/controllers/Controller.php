<?php

require_once __DIR__.'/../models/Permission.php';

use Twig\Extra\Markdown\ErusevMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

abstract class Controller
{
    protected $twig;
    protected $commentNotificationEmail;

    public function __construct()
    {
        /* templates chargés à partir du système de fichiers (répertoire vue) */
        $loader = new Twig\Loader\FilesystemLoader('app/views');

        $this->twig = new Twig\Environment($loader, ['cache' => false]);

        $this->twig->addExtension(new MarkdownExtension());

        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader([
            MarkdownRuntime::class => function () {
                return new MarkdownRuntime(new ErusevMarkdown());
            },
        ]));

        $user = SessionManager::getInstance()->get('user');
        $this->twig->addGlobal('user', $user);
        $user_id = $user['id'] ?? null;
        $this->twig->addGlobal('menu', $this->createMenu($user_id));

        $this->commentNotificationEmail = 'baptiste.cleyet@etu.univ-lyon1.fr';
    }

    private function createMenu($user_id)
    {
        if ($user_id == null) {
            $userGestion = false;
            $commentGestion = false;
        } else {
            $permissionModel = new Permission();
            $userGestion = $permissionModel->checkPermission($user_id, 'utilisateur_gerer');
            $commentGestion = $permissionModel->checkPermission($user_id, 'commentaire_gerer');
        }
        $menu = [
            'articlesList' => [
                'url' => 'app.php?route=articlesList',
                'label' => 'Accueil',
                'disabled' => false,
                'page' => 'articlesList.twig',
            ],
            'gestionUtilisateurs' => [
                'url' => 'app.php?route=usersList',
                'label' => 'Gestion utilisateurs',
                'disabled' => !$userGestion,
                'page' => 'usersList.twig',
            ],
            'gestionCommentaires' => [
                'url' => 'app.php?route=manageComments',
                'label' => 'Gestion commentaires',
                'disabled' => !$commentGestion,
                'page' => 'manageComments.twig',
            ],
        ];

        return $menu;
    }
}
