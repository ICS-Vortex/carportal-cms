<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add labor_price_uah to maintenance procedures, schedule items, and vehicle plan items';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE maintenance_procedures ADD labor_price_uah INT DEFAULT NULL');
        $this->addSql('ALTER TABLE maintenance_schedule_items ADD labor_price_uah INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items ADD labor_price_uah INT DEFAULT NULL');

        $this->addSql('
            UPDATE vehicle_maintenance_plan_items AS vmpi
            SET labor_price_uah = priced.labor_price_uah
            FROM (
                SELECT
                    vmpi_inner.id AS plan_item_id,
                    COALESCE(msi.labor_price_uah, mp.labor_price_uah) AS labor_price_uah
                FROM vehicle_maintenance_plan_items vmpi_inner
                INNER JOIN vehicle_maintenance_plans vmp ON vmp.id = vmpi_inner.plan_id
                INNER JOIN maintenance_schedule_items msi
                    ON msi.template_id = vmp.template_id
                    AND msi.procedure_id = vmpi_inner.procedure_id
                    AND msi.deleted = false
                INNER JOIN maintenance_procedures mp ON mp.id = vmpi_inner.procedure_id
            ) AS priced
            WHERE vmpi.id = priced.plan_item_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items DROP labor_price_uah');
        $this->addSql('ALTER TABLE maintenance_schedule_items DROP labor_price_uah');
        $this->addSql('ALTER TABLE maintenance_procedures DROP labor_price_uah');
    }
}
