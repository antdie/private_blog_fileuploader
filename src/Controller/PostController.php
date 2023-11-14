<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Form\PostCommentFormType;
use App\Repository\PostCategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/news')]
class PostController extends AbstractController
{
    #[Route('/{!page<\d+>?1}', name: 'app_post')]
    public function index(int $page, PostCategoryRepository $postCategoryRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $categories = $postCategoryRepository->findAll();

        $query = $postRepository->queryForPagination();
        $posts = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            $this->getParameter('app.posts_per_page') /*limit per page*/
        );

        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'categories' => $categories,
            'posts' => $posts
        ]);
    }

    #[Route('/{slug}/{id}', name: 'app_post_show')]
    #[ParamConverter('post', options: ['mapping' => ['slug' => 'slug', 'id' => 'id']])]
    public function show(Post $post, PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $next = $postRepository->findNextPost($post);
        $prev = $postRepository->findPreviousPost($post);

        $form = null;
        if ($this->isGranted('ROLE_USER')) {
            $comment = new PostComment();
            $form = $this->createForm(PostCommentFormType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setAccount($this->getUser())
                    ->setPost($post);
                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Your comment has been posted.');
                return $this->redirectToRoute('app_post_show', [
                    'slug' => $post->getSlug(),
                    'id' => $post->getId()
                ]);
            }
        }

        return $this->renderForm('post/show.html.twig', [
            'controller_name' => 'PostController',
            'post' => $post,
            'next' => $next,
            'prev' => $prev,
            'comments' => $post->getPostComments(),
            'form' => $form
        ]);
    }
}
