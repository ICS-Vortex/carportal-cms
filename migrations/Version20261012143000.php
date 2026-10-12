<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The Node API inserts rows without ids or timestamps and expects Postgres defaults.
 * Doctrine generates those values in PHP, so the shared database needs the same defaults.
 */
final class Version20261012143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uuid and timestamp defaults so the API can write the CMS database.';
    }

    public function up(Schema $schema): void
    {
        $timestamped = [
            'users',
            'auth_refresh_sessions',
            'maintenance_plan_requests',
            'maintenance_procedures',
            'maintenance_schedule_items',
            'maintenance_schedule_templates',
            'reminders',
            'vehicle_brands',
            'vehicle_configurations',
            'vehicle_generations',
            'vehicle_maintenance_plan_items',
            'vehicle_models',
            'vehicles',
        ];

        foreach ($timestamped as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN id SET DEFAULT gen_random_uuid()', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN created_at SET DEFAULT now()', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN updated_at SET DEFAULT now()', $table));
        }

        $this->addSql('ALTER TABLE service_logs ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->addSql('ALTER TABLE service_logs ALTER COLUMN created_at SET DEFAULT now()');
        $this->addSql('ALTER TABLE service_log_items ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->addSql('ALTER TABLE service_log_items ALTER COLUMN created_at SET DEFAULT now()');
        $this->addSql('ALTER TABLE user_google_accounts ALTER COLUMN created_at SET DEFAULT now()');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN assigned_at SET DEFAULT now()');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN updated_at SET DEFAULT now()');
    }

    public function down(Schema $schema): void
    {
        $timestamped = [
            'users',
            'auth_refresh_sessions',
            'maintenance_plan_requests',
            'maintenance_procedures',
            'maintenance_schedule_items',
            'maintenance_schedule_templates',
            'reminders',
            'vehicle_brands',
            'vehicle_configurations',
            'vehicle_generations',
            'vehicle_maintenance_plan_items',
            'vehicle_models',
            'vehicles',
        ];

        foreach ($timestamped as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN id DROP DEFAULT', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN created_at DROP DEFAULT', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER COLUMN updated_at DROP DEFAULT', $table));
        }

        $this->addSql('ALTER TABLE service_logs ALTER COLUMN id DROP DEFAULT');
        $this->addSql('ALTER TABLE service_logs ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE service_log_items ALTER COLUMN id DROP DEFAULT');
        $this->addSql('ALTER TABLE service_log_items ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE user_google_accounts ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN id DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN assigned_at DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ALTER COLUMN updated_at DROP DEFAULT');
    }
}
