<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceScheduleTemplate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceScheduleTemplate> */
final class MaintenanceScheduleTemplateCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MaintenanceScheduleTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.template.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.template.plural', domain: 'messages'))
            ->setPageTitle(Crud::PAGE_DETAIL, static fn (?MaintenanceScheduleTemplate $entity): string => $entity?->getNameUk() ?? '')
            ->setDefaultSort(['nameUk' => 'ASC'])
            ->setSearchFields(['nameUk'])
            ->setDefaultRowAction(Action::DETAIL)
            ->setHelp(Crud::PAGE_INDEX, t('entity.template.index_help', domain: 'messages'))
            ->overrideTemplate('crud/detail', 'admin/crud/maintenance_schedule_template_detail.html.twig');
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        if ($responseParameters->get('pageName') !== Crud::PAGE_DETAIL) {
            return $responseParameters;
        }

        $entityDto = $responseParameters->get('entity');
        $instance = $entityDto?->getInstance();
        if (!$instance instanceof MaintenanceScheduleTemplate) {
            return $responseParameters;
        }

        $responseParameters->set(
            'scheduleItemsIndexUrl',
            $this->adminUrlGenerator
                ->unsetAll()
                ->setController(MaintenanceScheduleItemCrudController::class)
                ->setAction(Action::INDEX)
                ->set('filters', [
                    'template' => [
                        'comparison' => '=',
                        'value' => $instance->getId(),
                    ],
                ])
                ->generateUrl(),
        );

        return $responseParameters;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action) => $action
                ->setLabel(t('entity.template.view_schedule', domain: 'messages'))
                ->setIcon('fa-solid fa-list-check')
                ->asPrimaryAction())
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_DETAIL) {
            return [];
        }

        yield TextField::new('nameUk', t('entity.template.name', domain: 'messages'));
        yield TextField::new('vehicleContextLabel', t('entity.template.vehicle', domain: 'messages'))
            ->hideOnForm()
            ->setHelp(t('entity.template.vehicle_help', domain: 'messages'));
        yield TextField::new('scheduleWorksIndexLink', t('entity.template.works_count', domain: 'messages'))
            ->onlyOnIndex()
            ->formatValue(function (mixed $value, MaintenanceScheduleTemplate $template): string {
                $url = $this->detailUrl($template);
                $count = $template->getActiveScheduleItemsCount();
                $open = $this->translator->trans('entity.template.open_schedule', domain: 'messages');

                return sprintf(
                    '<a href="%s" class="fw-semibold text-decoration-none">%d</a> <span class="text-muted">·</span> <a href="%s">%s</a>',
                    htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    $count,
                    htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    htmlspecialchars($open, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                );
            })
            ->renderAsHtml();
        yield AssociationField::new('generation', t('entity.template.generation', domain: 'messages'))
            ->hideOnIndex();
        yield AssociationField::new('configuration', t('entity.template.configuration', domain: 'messages'))
            ->hideOnIndex();
        yield AssociationField::new('station', t('entity.template.station', domain: 'messages'))
            ->hideOnIndex()
            ->setHelp(t('entity.template.station_help', domain: 'messages'));
        yield TextareaField::new('notesUk', t('entity.template.notes', domain: 'messages'))->hideOnIndex();
        yield BooleanField::new('deleted')->hideOnIndex();
    }

    private function detailUrl(MaintenanceScheduleTemplate $template): string
    {
        return $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($template->getId())
            ->generateUrl();
    }
}
