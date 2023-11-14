<?php

namespace App\Controller;

use App\Repository\FileRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('', name: 'app_index')]
    public function index(PostRepository $postRepository, FileRepository $fileRepository): Response
    {
        $posts = $postRepository->findBy([], ['id' => 'DESC'], 3);
        if ($this->isGranted('ROLE_USER')) {
            $files = $fileRepository->findBy([], ['id' => 'DESC'], 6);
        } else {
            $files = $fileRepository->findBy(['private' => false], ['id' => 'DESC'], 6);
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'posts' => $posts,
            'files' => $files
        ]);
    }
}
