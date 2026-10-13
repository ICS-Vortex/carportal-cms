<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<User> */
final class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.user.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.user.plural', domain: 'messages'))
            ->setDefaultSort(['fullName' => 'ASC'])
            ->setSearchFields(['email', 'fullName']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('fullName', t('account.field.full_name', domain: 'messages'));
        yield TextField::new('email', t('account.field.email', domain: 'messages'));
        yield TextField::new('locale', t('account.field.language', domain: 'messages'))->hideOnIndex();
        yield ChoiceField::new('role', t('entity.user.role', domain: 'messages'))->setChoices([
            $this->translator->trans('entity.user.role_user', domain: 'messages') => 'user',
            $this->translator->trans('entity.user.role_admin', domain: 'messages') => 'admin',
        ]);
        yield AssociationField::new('managedServiceStations', t('entity.user.managed_stations', domain: 'messages'))
            ->setHelp(t('entity.user.managed_stations_help', domain: 'messages'))
            ->onlyOnForms();
        yield BooleanField::new('isBlocked', t('entity.user.blocked', domain: 'messages'));
        yield BooleanField::new('deleted');
        yield TextField::new('plainPassword', t('account.field.new_password', domain: 'messages'))
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
