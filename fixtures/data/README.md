# Maintenance fixture data

## Honda HR-V (RU1)

- **Source:** `HR-V.xlsx` — регламент ТО HR-V (моделі RU1)
- **Generated file:** `honda-hrv-ru1.json`
- **Regenerate** after updating the spreadsheet:

```bash
python3 scripts/generate-hrv-ru1-fixtures.py /path/to/HR-V.xlsx
```

- **Load into the CMS database** (idempotent; safe to re-run):

```bash
docker compose exec cms php bin/console doctrine:fixtures:load --append --no-interaction --group=catalog --group=maintenance
```

(`HondaHrvRu1MaintenanceFixture` входить у групи `catalog` та `maintenance`; окремий `--group=honda-hrv` більше не потрібен.)

Creates Honda → HR-V → RU1 catalog entries, **28** maintenance procedures, and two schedule templates (CVT and МКПП) with interval km/months derived from the service matrix.

Each procedure includes **`labor_price_uah`** (орієнтовна вартість робіт без запчастин). Edit prices in CMS under **Роботи** or regenerate from the spreadsheet script.

### How procedures link to cars

Procedures are **not** tied to one car row. The link is many-to-many:

`Робота` → `Пункт шаблону` (`maintenance_schedule_items`) → `Шаблон регламенту` → **покоління / комплектація** → конкретні авто в гаражі.

One робота (e.g. «Заміна моторної оливи») can appear in several templates (HR-V CVT, Civic, …) with different intervals and prices. Manage links in CMS: **Пункти шаблонів**.
