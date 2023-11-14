<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private $secret;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->secret = $parameterBag->get('app.api_secret');
    }

    #[Route('/registration', name: 'app_registration', methods: 'POST')]
    public function registration(Request $request, AccountRepository $accountRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $response = new Response();
        $account = new Account();

        $secret = $request->request->get('secret');
        $discordId = $request->request->get('discord_id');
        $username = $request->request->get('username');
        if (!$secret || !$discordId || !$username || $secret !== $this->secret) {
            return $response->setStatusCode(400);
        }

        $password = openssl_random_pseudo_bytes(3);
        $password = bin2hex($password);

        $account->setDiscordId($discordId)
            ->setUsername($username)
            ->setPassword(
                $userPasswordHasher->hashPassword(
                    $account,
                    $password
                )
            );

        $errors = $validator->validate($account);
        if (count($errors) > 0) {
            $response->setContent(json_encode(['type' => 'error', 'message' => 'Username already used or you already have an account.']));
            return $response->setStatusCode(400);
        }

        $entityManager->persist($account);
        $entityManager->flush();

        $response->setContent(json_encode(['type' => 'success', 'username' => $username, 'password' => $password]));
        return $response->setStatusCode(200);
    }

    #[Route('/lost-password', name: 'app_lost_password', methods: 'POST')]
    public function resetPassword(Request $request, AccountRepository $accountRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $response = new Response();

        $secret = $request->request->get('secret');
        $discordId = $request->request->get('discord_id');
        if (!$secret || !$discordId || $secret !== $this->secret) {
            return $response->setStatusCode(400);
        }

        $account = $accountRepository->findOneByDiscordId($discordId);
        if (!$account) {
            $response->setContent(json_encode(['type' => 'error', 'message' => 'You don\'t have an account.']));
            return $response->setStatusCode(400);
        }

        $password = openssl_random_pseudo_bytes(3);
        $password = bin2hex($password);

        $account->setPassword(
            $userPasswordHasher->hashPassword(
                $account,
                $password
            )
        );
        $entityManager->persist($account);
        $entityManager->flush();

        $response->setContent(json_encode(['type' => 'success', 'password' => $password]));
        return $response->setStatusCode(200);
    }

    #[Route('/lost-username', name: 'app_lost_username', methods: 'POST')]
    public function lostUsername(Request $request, AccountRepository $accountRepository, EntityManagerInterface $entityManager): Response
    {
        $response = new Response();

        $secret = $request->request->get('secret');
        $discordId = $request->request->get('discord_id');
        if (!$secret || !$discordId || $secret !== $this->secret) {
            return $response->setStatusCode(400);
        }

        $account = $accountRepository->findOneByDiscordId($discordId);
        if (!$account) {
            $response->setContent(json_encode(['type' => 'error', 'message' => 'You don\'t have an account.']));
            return $response->setStatusCode(400);
        }

        $response->setContent(json_encode(['type' => 'success', 'username' => (string) $account]));
        return $response->setStatusCode(200);
    }
}
