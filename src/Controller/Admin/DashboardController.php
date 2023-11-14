<?php

namespace App\Controller\Admin;

use App\Entity\PostCategory;
use App\Entity\PostComment;
use App\Entity\Post;
use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Repository\FileRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private $accountRepository;
    private $postRepository;
    private $postCommentRepository;
    private $fileRepository;

    public function __construct(AccountRepository $accountRepository, PostRepository $postRepository, PostCommentRepository $postCommentRepository, FileRepository $fileRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->postRepository = $postRepository;
        $this->postCommentRepository = $postCommentRepository;
        $this->fileRepository = $fileRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $totalAccounts = $this->accountRepository->count([]);
        $totalPosts = $this->postRepository->count([]);
        $totalComments = $this->postCommentRepository->count([]);
        $totalFiles = $this->fileRepository->count([]);
        $totalSizeUsed = $this->fileRepository->totalSizeUsed();

         return $this->render('admin/index.html.twig', [
             'total_accounts' => $totalAccounts,
             'total_posts' => $totalPosts,
             'total_comments' => $totalComments,
             'total_files' => $totalFiles,
             'total_size_used' => $totalSizeUsed,
         ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->getParameter('app.site_name'));
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'mb-1 fa fa-home');
        yield MenuItem::linkToCrud('Accounts', 'mb-1 fa fa-user', Account::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Categories', 'mb-1 fa fa-cat', PostCategory::class)->setPermission('ROLE_SUPER_ADMIN');;
        yield MenuItem::linkToCrud('Posts', 'mb-1 fa fa-pen', Post::class);
        yield MenuItem::linkToCrud('Comments', 'mb-1 fa fa-comments', PostComment::class);
        yield MenuItem::linktoRoute('Go back', 'mb-1 fa fa-arrow-left', 'app_index');
    }
}
