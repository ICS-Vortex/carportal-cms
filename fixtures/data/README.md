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
docker compose exec cms php bin/console doctrine:fixtures:load --append --no-interaction --group=honda-hrv
```

Creates Honda → HR-V → RU1 catalog entries, **28** maintenance procedures, and two schedule templates (CVT and МКПП) with interval km/months derived from the service matrix.

Each procedure includes **`labor_price_uah`** (орієнтовна вартість робіт без запчастин). Edit prices in CMS under **Роботи** or regenerate from the spreadsheet script.
