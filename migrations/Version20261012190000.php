<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize vehicle_configurations.fuel_type to FuelType enum values';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE vehicle_configurations SET fuel_type = 'gas' WHERE fuel_type IS NOT NULL AND lower(trim(fuel_type)) IN ('gas', 'lpg', 'газ', 'газовий')");
        $this->addSql("UPDATE vehicle_configurations SET fuel_type = 'benzin' WHERE fuel_type IS NOT NULL AND lower(trim(fuel_type)) IN ('benzin', 'petrol', 'gasoline', 'бензин', 'бензиновий')");
        $this->addSql("UPDATE vehicle_configurations SET fuel_type = 'diezel' WHERE fuel_type IS NOT NULL AND lower(trim(fuel_type)) IN ('diezel', 'diesel', 'дизель', 'дизельний')");
        $this->addSql("UPDATE vehicle_configurations SET fuel_type = 'hybrid' WHERE fuel_type IS NOT NULL AND lower(trim(fuel_type)) IN ('hybrid', 'гібрид', 'гібридний')");
    }

    public function down(Schema $schema): void
    {
        // Irreversible: legacy free-text / localized values are not recoverable.
    }
}
