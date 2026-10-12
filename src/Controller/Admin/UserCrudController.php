<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<User> */
final class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.user.singular'))
            ->setEntityLabelInPlural(t('entity.user.plural'))
            ->setDefaultSort(['fullName' => 'ASC'])
            ->setSearchFields(['email', 'fullName']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('fullName')->setLabel('Name');
        yield TextField::new('email');
        yield TextField::new('locale')->hideOnIndex();
        yield ChoiceField::new('role')->setChoices([
            'User' => 'user',
            'Admin' => 'admin',
        ]);
        yield BooleanField::new('isBlocked')->setLabel('Blocked');
        yield BooleanField::new('deleted');
        yield TextField::new('plainPassword', 'Password')
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->onlyOnForms();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPlainPassword(object $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $plainPassword = $user->getPlainPassword();
        if (null === $plainPassword || '' === $plainPassword) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setPlainPassword(null);
    }
}
