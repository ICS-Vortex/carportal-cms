<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenancePlanImport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenancePlanImport> */
final class MaintenancePlanImportCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MaintenancePlanImport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.plan_import.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.plan_import.plural', domain: 'messages'))
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['originalFilename', 'status']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $planRequestsUrl = $this->adminUrlGenerator
            ->setController(MaintenancePlanRequestCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        $startImport = Action::new('startPlanImport', t('entity.plan_import.empty_cta', domain: 'messages'))
            ->linkToUrl($planRequestsUrl)
            ->setIcon('fa-solid fa-file-upload')
            ->setCssClass('btn btn-primary')
            ->createAsGlobalAction();

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $startImport)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('planRequest', t('entity.plan_request.singular', domain: 'messages'));
        yield AssociationField::new('vehicle', t('dashboard.table_vehicle', domain: 'messages'));
        yield AssociationField::new('uploadedBy', t('entity.plan_import.uploaded_by', domain: 'messages'))->hideOnIndex();
        yield TextField::new('originalFilename', t('entity.plan_import.filename', domain: 'messages'));
        yield TextField::new('statusLabel', t('entity.plan_request.status', domain: 'messages'))
            ->hideOnForm();
        yield IntegerField::new('rowsTotal', t('entity.plan_import.rows_total', domain: 'messages'));
        yield IntegerField::new('rowsImported', t('entity.plan_import.rows_imported', domain: 'messages'));
        yield TextareaField::new('errorMessage', t('entity.plan_import.error', domain: 'messages'))->hideOnIndex();
        yield TextareaField::new('logText', t('entity.plan_import.log', domain: 'messages'))->onlyOnDetail();
        yield DateTimeField::new('createdAt', t('entity.plan_request.created_at', domain: 'messages'));
        yield DateTimeField::new('startedAt', t('entity.plan_import.started_at', domain: 'messages'))->hideOnIndex();
        yield DateTimeField::new('finishedAt', t('entity.plan_import.finished_at', domain: 'messages'))->hideOnIndex();
    }
}
