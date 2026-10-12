<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AccountSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
#[AdminRoute('/account', name: 'account')]
final class AccountSettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[AdminRoute('/', name: 'index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $loginEmail = $user->getEmail();

        $form = $this->createForm(AccountSettingsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($loginEmail);
            $passwordError = $this->applyPasswordChange($form, $user);
            if (null !== $passwordError) {
                $this->addFlash('danger', $passwordError);

                return $this->render('admin/account_settings.html.twig', [
                    'form' => $form,
                ]);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'account.flash.saved');

            return $this->redirectToRoute('admin_account_index');
        }

        return $this->render('admin/account_settings.html.twig', [
            'form' => $form,
        ]);
    }

    /** @param FormInterface<User> $form */
    private function applyPasswordChange(FormInterface $form, User $user): ?string
    {
        $newPassword = $form->get('newPassword')->getData();
        if (null === $newPassword || '' === $newPassword) {
            return null;
        }

        $currentPassword = $form->get('currentPassword')->getData();
        if (!is_string($currentPassword) || '' === $currentPassword) {
            return 'account.error.current_password_required';
        }

        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return 'account.error.current_password_invalid';
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));

        return null;
    }
}
