<?php

namespace App\Controller;

use App\Form\EditPasswordFormType;
use App\Repository\FileRepository;
use App\Repository\PostCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, PostCommentRepository $postCommentRepository, FileRepository $fileRepository): Response
    {
        $user = $this->getUser();
        $totalUserComments = $postCommentRepository->count(['account' => $user]);
        $totalUserFiles = $fileRepository->count(['account' => $user]);

        $user = $this->getUser();
        $form = $this->createForm(EditPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            if ($userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('plainPassword')->getData();
                $password = $userPasswordHasher->hashPassword($user, $newPassword);

                $user->setPassword($password);
                $entityManager->flush();

                $this->addFlash('success', 'Your password has been modified.');
                return $this->redirectToRoute('app_account');
            } else {
                $form->get('oldPassword')->addError(new FormError('Your old password\'s wrong, please try again.'));
            }
        }

        return $this->renderForm('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'form' => $form,
            'total_user_comments' => $totalUserComments,
            'total_user_files' => $totalUserFiles
        ]);
    }
}
