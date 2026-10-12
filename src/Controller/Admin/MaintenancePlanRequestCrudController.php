<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenancePlanRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenancePlanRequest> */
final class MaintenancePlanRequestCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MaintenancePlanRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.plan_request.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.plan_request.plural', domain: 'messages'))
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $import = Action::new('importPlan', t('entity.plan_request.import_upload', domain: 'messages'))
            ->linkToRoute('admin_plan_request_import', static fn (MaintenancePlanRequest $entity): array => [
                'id' => (string) $entity->getId(),
            ])
            ->setIcon('fa-solid fa-file-upload')
            ->asSuccessAction();

        return $actions
            ->add(Crud::PAGE_EDIT, $import)
            ->add(Crud::PAGE_DETAIL, $import);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', t('dashboard.table_owner', domain: 'messages'));
        yield AssociationField::new('vehicle', t('dashboard.table_vehicle', domain: 'messages'));
        yield ChoiceField::new('status', t('entity.plan_request.status', domain: 'messages'))->setChoices([
            $this->translator->trans('entity.plan_request.status_open', domain: 'messages') => 'open',
            $this->translator->trans('entity.plan_request.status_resolved', domain: 'messages') => 'resolved',
        ]);
        yield DateTimeField::new('createdAt', t('entity.plan_request.created_at', domain: 'messages'))->hideOnForm();
        yield TextareaField::new('requestNote', t('entity.plan_request.note', domain: 'messages'))->hideOnIndex();
        yield AssociationField::new('imports', t('entity.plan_import.plural', domain: 'messages'))
            ->onlyOnDetail()
            ->setCrudController(MaintenancePlanImportCrudController::class);
    }
}
