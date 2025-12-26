<?php

require_once __DIR__.'/SessionManager.php';
require_once __DIR__.'/../models/Comment.php';
require_once __DIR__.'/Controller.php';
class ManageCommentController extends Controller
{
    // Constructeur
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $commentModel = new Comment();

        $search = $_GET['search'] ?? null;

        if ($search) { // si on a recherché quelque chose
            $commentsList = $commentModel->search_comments_with_user_or_content($search);
        } else { // pas de recherceh
            $commentsList = $commentModel->allComments();
        }
        $user = SessionManager::getInstance()->get('user');
        $user_id = $user['id'];

        echo $this->twig->render('ManageComments.twig', [
            'currentPage' => 'ManageComments.twig',
            'titre_doc' => 'Gestion des commentaires',
            'commentsList' => $commentsList,
            'user_id' => $user_id,
            'currentSearch' => $search,
        ]);
    }
}
