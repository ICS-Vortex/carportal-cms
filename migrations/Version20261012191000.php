<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize vehicle_configurations.transmission_label to TransmissionType enum values';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'ecvt' WHERE lower(trim(transmission_label)) IN ('e-cvt', 'ecvt', 'cvt-hybrid')");
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'cvt' WHERE lower(trim(transmission_label)) = 'cvt'");
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'manual' WHERE lower(trim(transmission_label)) IN ('manual', 'mt', 'механічна', 'механіка', 'мкпп')");
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'auto' WHERE lower(trim(transmission_label)) IN ('auto', 'automatic', 'at', '6at', '8at', '9at', '10at', 'акпп', 'автомат') OR transmission_label ~* '^[0-9]+at$'");
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'dct' WHERE lower(trim(transmission_label)) IN ('dct', 'dsg', 'pdk', 'робот')");
        $this->addSql("UPDATE vehicle_configurations SET transmission_label = 'amt' WHERE lower(trim(transmission_label)) IN ('amt', 'semi-auto', 'semi automatic', 'роботизована')");
    }

    public function down(Schema $schema): void
    {
        // Irreversible: legacy free-text values are not recoverable.
    }
}
