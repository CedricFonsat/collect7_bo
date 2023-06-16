<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(Request $request): User|JsonResponse
    {
        $dataUser = json_decode($request->getContent(), true);
        $existingUser = $this->em->getRepository(User::class)->findOneByEmail($dataUser['email']);

        if ($existingUser) {
            return $this->json([
                'error' => true,
                'code' => 'existing_email'
            ]);
        }

        $user = new User();
        $user->setEmail($dataUser['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dataUser['password']));
        $user->setRoles([]);

        $this->em->persist($user);
        $this->em->flush();

        return $user;

    }
}