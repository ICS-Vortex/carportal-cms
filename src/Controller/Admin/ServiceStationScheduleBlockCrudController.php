<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ServiceStationScheduleBlock;
use App\Entity\User;
use App\Service\Admin\ServiceStationAccess;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<ServiceStationScheduleBlock> */
final class ServiceStationScheduleBlockCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if ($user instanceof User) {
            $this->stationAccess->applyStationIdFilter($queryBuilder, $user, 'entity.station');
        }

        return $queryBuilder;
    }

    public static function getEntityFqcn(): string
    {
        return ServiceStationScheduleBlock::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.schedule_block.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.schedule_block.plural', domain: 'messages'))
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setSearchFields(['labelUk']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('station', t('entity.schedule_block.station', domain: 'messages'))
            ->setHelp(t('entity.schedule_block.station_help', domain: 'messages'));
        yield TextField::new('labelUk', t('entity.schedule_block.label', domain: 'messages'));
        yield DateField::new('startDate', t('entity.schedule_block.start_date', domain: 'messages'));
        yield DateField::new('endDate', t('entity.schedule_block.end_date', domain: 'messages'))
            ->setHelp(t('entity.schedule_block.date_help', domain: 'messages'));
        yield TimeField::new('startTime', t('entity.schedule_block.start_time', domain: 'messages'))
            ->setHelp(t('entity.schedule_block.time_help', domain: 'messages'));
        yield TimeField::new('endTime', t('entity.schedule_block.end_time', domain: 'messages'));
    }
}
