<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012120835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial Car Portal schema from the backend catalog, garage, maintenance, and auth tables.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_refresh_sessions (family_id UUID NOT NULL, token_hash TEXT NOT NULL, user_agent TEXT DEFAULT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, revoked_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, last_used_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id UUID NOT NULL, replaced_by_session_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7476673EB3BC57DA ON auth_refresh_sessions (token_hash)');
        $this->addSql('CREATE INDEX idx_auth_refresh_sessions_user_id ON auth_refresh_sessions (user_id)');
        $this->addSql('CREATE INDEX idx_auth_refresh_sessions_family_id ON auth_refresh_sessions (family_id)');
        $this->addSql('CREATE INDEX idx_auth_refresh_sessions_expires_at ON auth_refresh_sessions (expires_at)');
        $this->addSql('CREATE INDEX IDX_7476673EAB4EB930 ON auth_refresh_sessions (replaced_by_session_id)');
        $this->addSql('CREATE TABLE maintenance_plan_requests (status TEXT DEFAULT \'open\' NOT NULL, request_note TEXT DEFAULT NULL, handled_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id UUID NOT NULL, vehicle_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_maintenance_plan_requests_status_created_at ON maintenance_plan_requests (status, created_at)');
        $this->addSql('CREATE INDEX IDX_4D709BFEA76ED395 ON maintenance_plan_requests (user_id)');
        $this->addSql('CREATE INDEX IDX_4D709BFE545317D1 ON maintenance_plan_requests (vehicle_id)');
        $this->addSql('CREATE TABLE maintenance_procedures (code TEXT NOT NULL, title_uk TEXT NOT NULL, description_uk TEXT DEFAULT NULL, default_interval_km INT DEFAULT NULL, default_interval_months INT DEFAULT NULL, category TEXT NOT NULL, deleted BOOLEAN DEFAULT false NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B1DD7ECC77153098 ON maintenance_procedures (code)');
        $this->addSql('CREATE INDEX idx_maintenance_procedures_deleted ON maintenance_procedures (deleted)');
        $this->addSql('CREATE TABLE maintenance_schedule_items (interval_km INT DEFAULT NULL, interval_months INT DEFAULT NULL, notes_uk TEXT DEFAULT NULL, deleted BOOLEAN DEFAULT false NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, template_id UUID NOT NULL, procedure_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_maintenance_schedule_items_deleted ON maintenance_schedule_items (deleted)');
        $this->addSql('CREATE UNIQUE INDEX uniq_maintenance_schedule_items_template_procedure ON maintenance_schedule_items (template_id, procedure_id)');
        $this->addSql('CREATE INDEX IDX_EBC69F305DA0FB8 ON maintenance_schedule_items (template_id)');
        $this->addSql('CREATE INDEX IDX_EBC69F301624BCD2 ON maintenance_schedule_items (procedure_id)');
        $this->addSql('CREATE TABLE maintenance_schedule_templates (name_uk TEXT NOT NULL, notes_uk TEXT DEFAULT NULL, deleted BOOLEAN DEFAULT false NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, generation_id UUID NOT NULL, configuration_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_maintenance_schedule_templates_deleted ON maintenance_schedule_templates (deleted)');
        $this->addSql('CREATE INDEX idx_maintenance_schedule_templates_configuration_id ON maintenance_schedule_templates (configuration_id)');
        $this->addSql('CREATE INDEX IDX_F35B724553A6EC4 ON maintenance_schedule_templates (generation_id)');
        $this->addSql('CREATE TABLE reminders (due_date DATE DEFAULT NULL, due_mileage_km INT DEFAULT NULL, remind_days_before INT DEFAULT 30 NOT NULL, notify_in_app BOOLEAN DEFAULT true NOT NULL, notify_email BOOLEAN DEFAULT true NOT NULL, status TEXT DEFAULT \'upcoming\' NOT NULL, last_sent_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id UUID NOT NULL, vehicle_id UUID NOT NULL, procedure_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_reminders_user_due_date ON reminders (user_id, due_date)');
        $this->addSql('CREATE UNIQUE INDEX idx_reminders_user_vehicle_procedure ON reminders (user_id, vehicle_id, procedure_id)');
        $this->addSql('CREATE INDEX IDX_6D92B9D4A76ED395 ON reminders (user_id)');
        $this->addSql('CREATE INDEX IDX_6D92B9D4545317D1 ON reminders (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_6D92B9D41624BCD2 ON reminders (procedure_id)');
        $this->addSql('CREATE TABLE service_log_items (notes_uk TEXT DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, service_log_id UUID NOT NULL, procedure_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_99F1385AD4A058B0 ON service_log_items (service_log_id)');
        $this->addSql('CREATE INDEX IDX_99F1385A1624BCD2 ON service_log_items (procedure_id)');
        $this->addSql('CREATE TABLE service_logs (service_date DATE NOT NULL, mileage_km INT NOT NULL, service_station TEXT DEFAULT NULL, notes_uk TEXT DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, vehicle_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_service_logs_vehicle_id ON service_logs (vehicle_id)');
        $this->addSql('CREATE TABLE user_google_accounts (google_sub TEXT NOT NULL, email TEXT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC31900BE788A50 ON user_google_accounts (google_sub)');
        $this->addSql('CREATE TABLE users (email TEXT NOT NULL, password_hash TEXT DEFAULT NULL, full_name TEXT NOT NULL, avatar_url TEXT DEFAULT NULL, locale TEXT DEFAULT \'uk\' NOT NULL, role TEXT DEFAULT \'user\' NOT NULL, is_blocked BOOLEAN DEFAULT false NOT NULL, blocked_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, deleted BOOLEAN DEFAULT false NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX idx_users_role ON users (role)');
        $this->addSql('CREATE INDEX idx_users_is_blocked ON users (is_blocked)');
        $this->addSql('CREATE INDEX idx_users_deleted ON users (deleted)');
        $this->addSql('CREATE TABLE vehicle_brands (slug TEXT NOT NULL, name TEXT NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D77C45E7989D9B62 ON vehicle_brands (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D77C45E75E237E06 ON vehicle_brands (name)');
        $this->addSql('CREATE TABLE vehicle_configurations (slug TEXT NOT NULL, engine_label TEXT NOT NULL, transmission_label TEXT NOT NULL, drivetrain_label TEXT DEFAULT NULL, fuel_type TEXT DEFAULT NULL, notes_uk TEXT DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, generation_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_vehicle_configurations_generation_id ON vehicle_configurations (generation_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_configurations_generation_slug ON vehicle_configurations (generation_id, slug)');
        $this->addSql('CREATE TABLE vehicle_generations (slug TEXT NOT NULL, name TEXT NOT NULL, year_from INT NOT NULL, year_to INT NOT NULL, notes_uk TEXT DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, model_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_vehicle_generations_model_id ON vehicle_generations (model_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_generations_model_slug ON vehicle_generations (model_id, slug)');
        $this->addSql('CREATE TABLE vehicle_maintenance_plan_items (procedure_title TEXT NOT NULL, procedure_category TEXT NOT NULL, interval_km INT DEFAULT NULL, interval_months INT DEFAULT NULL, notes_uk TEXT DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, status TEXT DEFAULT \'pending\' NOT NULL, last_completed_at DATE DEFAULT NULL, last_completed_mileage_km INT DEFAULT NULL, baseline_date DATE DEFAULT CURRENT_DATE NOT NULL, baseline_mileage_km INT DEFAULT 0 NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, plan_id UUID NOT NULL, procedure_id UUID DEFAULT NULL, last_service_log_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_vehicle_maintenance_plan_items_plan_id ON vehicle_maintenance_plan_items (plan_id)');
        $this->addSql('CREATE INDEX IDX_6A0A73401624BCD2 ON vehicle_maintenance_plan_items (procedure_id)');
        $this->addSql('CREATE INDEX IDX_6A0A7340BAA75134 ON vehicle_maintenance_plan_items (last_service_log_id)');
        $this->addSql('CREATE TABLE vehicle_maintenance_plans (template_name TEXT NOT NULL, assigned_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, source_template_updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, id UUID NOT NULL, vehicle_id UUID NOT NULL, template_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B7C286A2545317D1 ON vehicle_maintenance_plans (vehicle_id)');
        $this->addSql('CREATE INDEX idx_vehicle_maintenance_plans_vehicle_id ON vehicle_maintenance_plans (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_B7C286A25DA0FB8 ON vehicle_maintenance_plans (template_id)');
        $this->addSql('CREATE TABLE vehicle_models (slug TEXT NOT NULL, name TEXT NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, brand_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_vehicle_models_brand_id ON vehicle_models (brand_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_models_brand_slug ON vehicle_models (brand_id, slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_models_brand_name ON vehicle_models (brand_id, name)');
        $this->addSql('CREATE TABLE vehicles (year INT NOT NULL, vin TEXT DEFAULT NULL, license_plate TEXT DEFAULT NULL, image_url TEXT DEFAULT NULL, mileage_km INT DEFAULT 0 NOT NULL, deleted BOOLEAN DEFAULT false NOT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id UUID NOT NULL, generation_id UUID NOT NULL, configuration_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FCE69FAB1085141 ON vehicles (vin)');
        $this->addSql('CREATE INDEX idx_vehicles_user_id ON vehicles (user_id)');
        $this->addSql('CREATE INDEX idx_vehicles_generation_id ON vehicles (generation_id)');
        $this->addSql('CREATE INDEX idx_vehicles_configuration_id ON vehicles (configuration_id)');
        $this->addSql('CREATE INDEX idx_vehicles_deleted ON vehicles (user_id, deleted)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE auth_refresh_sessions ADD CONSTRAINT FK_7476673EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE auth_refresh_sessions ADD CONSTRAINT FK_7476673EAB4EB930 FOREIGN KEY (replaced_by_session_id) REFERENCES auth_refresh_sessions (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_plan_requests ADD CONSTRAINT FK_4D709BFEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_plan_requests ADD CONSTRAINT FK_4D709BFE545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_schedule_items ADD CONSTRAINT FK_EBC69F305DA0FB8 FOREIGN KEY (template_id) REFERENCES maintenance_schedule_templates (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_schedule_items ADD CONSTRAINT FK_EBC69F301624BCD2 FOREIGN KEY (procedure_id) REFERENCES maintenance_procedures (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_schedule_templates ADD CONSTRAINT FK_F35B724553A6EC4 FOREIGN KEY (generation_id) REFERENCES vehicle_generations (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_schedule_templates ADD CONSTRAINT FK_F35B72473F32DD8 FOREIGN KEY (configuration_id) REFERENCES vehicle_configurations (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reminders ADD CONSTRAINT FK_6D92B9D4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reminders ADD CONSTRAINT FK_6D92B9D4545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE reminders ADD CONSTRAINT FK_6D92B9D41624BCD2 FOREIGN KEY (procedure_id) REFERENCES maintenance_procedures (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE service_log_items ADD CONSTRAINT FK_99F1385AD4A058B0 FOREIGN KEY (service_log_id) REFERENCES service_logs (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE service_log_items ADD CONSTRAINT FK_99F1385A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES maintenance_procedures (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE service_logs ADD CONSTRAINT FK_3ECC72B0545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_google_accounts ADD CONSTRAINT FK_DC31900BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_configurations ADD CONSTRAINT FK_78FA902A553A6EC4 FOREIGN KEY (generation_id) REFERENCES vehicle_generations (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_generations ADD CONSTRAINT FK_24ED7D357975B7E7 FOREIGN KEY (model_id) REFERENCES vehicle_models (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items ADD CONSTRAINT FK_6A0A7340E899029B FOREIGN KEY (plan_id) REFERENCES vehicle_maintenance_plans (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items ADD CONSTRAINT FK_6A0A73401624BCD2 FOREIGN KEY (procedure_id) REFERENCES maintenance_procedures (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items ADD CONSTRAINT FK_6A0A7340BAA75134 FOREIGN KEY (last_service_log_id) REFERENCES service_logs (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ADD CONSTRAINT FK_B7C286A2545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans ADD CONSTRAINT FK_B7C286A25DA0FB8 FOREIGN KEY (template_id) REFERENCES maintenance_schedule_templates (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_models ADD CONSTRAINT FK_4D0831DA44F5D008 FOREIGN KEY (brand_id) REFERENCES vehicle_brands (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FA553A6EC4 FOREIGN KEY (generation_id) REFERENCES vehicle_generations (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FA73F32DD8 FOREIGN KEY (configuration_id) REFERENCES vehicle_configurations (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicle_generations ADD CONSTRAINT vehicle_generations_year_range_check CHECK (year_from <= year_to)');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items ADD CONSTRAINT vehicle_maintenance_plan_items_status_check CHECK (status IN (\'pending\', \'done\', \'skipped\'))');
        $this->addSql('ALTER TABLE maintenance_plan_requests ADD CONSTRAINT maintenance_plan_requests_status_check CHECK (status IN (\'open\', \'resolved\'))');
        $this->addSql('CREATE UNIQUE INDEX idx_maintenance_schedule_templates_unique_configuration_active ON maintenance_schedule_templates (configuration_id) WHERE deleted = false');
        $this->addSql('CREATE UNIQUE INDEX idx_maintenance_plan_requests_vehicle_user_open ON maintenance_plan_requests (vehicle_id, user_id) WHERE status = \'open\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_refresh_sessions DROP CONSTRAINT FK_7476673EA76ED395');
        $this->addSql('ALTER TABLE auth_refresh_sessions DROP CONSTRAINT FK_7476673EAB4EB930');
        $this->addSql('ALTER TABLE maintenance_plan_requests DROP CONSTRAINT FK_4D709BFEA76ED395');
        $this->addSql('ALTER TABLE maintenance_plan_requests DROP CONSTRAINT FK_4D709BFE545317D1');
        $this->addSql('ALTER TABLE maintenance_schedule_items DROP CONSTRAINT FK_EBC69F305DA0FB8');
        $this->addSql('ALTER TABLE maintenance_schedule_items DROP CONSTRAINT FK_EBC69F301624BCD2');
        $this->addSql('ALTER TABLE maintenance_schedule_templates DROP CONSTRAINT FK_F35B724553A6EC4');
        $this->addSql('ALTER TABLE maintenance_schedule_templates DROP CONSTRAINT FK_F35B72473F32DD8');
        $this->addSql('ALTER TABLE reminders DROP CONSTRAINT FK_6D92B9D4A76ED395');
        $this->addSql('ALTER TABLE reminders DROP CONSTRAINT FK_6D92B9D4545317D1');
        $this->addSql('ALTER TABLE reminders DROP CONSTRAINT FK_6D92B9D41624BCD2');
        $this->addSql('ALTER TABLE service_log_items DROP CONSTRAINT FK_99F1385AD4A058B0');
        $this->addSql('ALTER TABLE service_log_items DROP CONSTRAINT FK_99F1385A1624BCD2');
        $this->addSql('ALTER TABLE service_logs DROP CONSTRAINT FK_3ECC72B0545317D1');
        $this->addSql('ALTER TABLE user_google_accounts DROP CONSTRAINT FK_DC31900BA76ED395');
        $this->addSql('ALTER TABLE vehicle_configurations DROP CONSTRAINT FK_78FA902A553A6EC4');
        $this->addSql('ALTER TABLE vehicle_generations DROP CONSTRAINT FK_24ED7D357975B7E7');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items DROP CONSTRAINT FK_6A0A7340E899029B');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items DROP CONSTRAINT FK_6A0A73401624BCD2');
        $this->addSql('ALTER TABLE vehicle_maintenance_plan_items DROP CONSTRAINT FK_6A0A7340BAA75134');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans DROP CONSTRAINT FK_B7C286A2545317D1');
        $this->addSql('ALTER TABLE vehicle_maintenance_plans DROP CONSTRAINT FK_B7C286A25DA0FB8');
        $this->addSql('ALTER TABLE vehicle_models DROP CONSTRAINT FK_4D0831DA44F5D008');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT FK_1FCE69FAA76ED395');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT FK_1FCE69FA553A6EC4');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT FK_1FCE69FA73F32DD8');
        $this->addSql('DROP TABLE auth_refresh_sessions');
        $this->addSql('DROP TABLE maintenance_plan_requests');
        $this->addSql('DROP TABLE maintenance_procedures');
        $this->addSql('DROP TABLE maintenance_schedule_items');
        $this->addSql('DROP TABLE maintenance_schedule_templates');
        $this->addSql('DROP TABLE reminders');
        $this->addSql('DROP TABLE service_log_items');
        $this->addSql('DROP TABLE service_logs');
        $this->addSql('DROP TABLE user_google_accounts');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE vehicle_brands');
        $this->addSql('DROP TABLE vehicle_configurations');
        $this->addSql('DROP TABLE vehicle_generations');
        $this->addSql('DROP TABLE vehicle_maintenance_plan_items');
        $this->addSql('DROP TABLE vehicle_maintenance_plans');
        $this->addSql('DROP TABLE vehicle_models');
        $this->addSql('DROP TABLE vehicles');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
