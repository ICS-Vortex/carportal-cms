<?php

declare(strict_types=1);

namespace App\Maintenance\PlanImport;

use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\SheetInterface;

final class MaintenancePlanSpreadsheetParser
{
    /**
     * @return list<ParsedPlanRow>
     */
    public function parse(string $absolutePath, string $extension): array
    {
        $extension = strtolower(ltrim($extension, '.'));

        return match ($extension) {
            'csv' => $this->parseCsv($absolutePath),
            'xlsx' => $this->parseSpreadsheet($absolutePath, true),
            'xls' => throw new \InvalidArgumentException('Формат .xls не підтримується. Збережіть файл як .xlsx або .csv.'),
            default => throw new \InvalidArgumentException(sprintf('Невідомий формат файлу: %s', $extension)),
        };
    }

    /**
     * @return list<ParsedPlanRow>
     */
    private function parseCsv(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Не вдалося відкрити CSV-файл.');
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $this->rowsToPlanItems($rows);
    }

    /**
     * @return list<ParsedPlanRow>
     */
    private function parseSpreadsheet(string $absolutePath, bool $xlsx): array
    {
        if (!class_exists(XlsxReader::class)) {
            throw new \RuntimeException('Бібліотека OpenSpout недоступна. Перезберіть CMS-образ (потрібне PHP ext-zip).');
        }

        $reader = $xlsx ? new XlsxReader() : new CsvReader();
        $reader->open($absolutePath);

        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            /** @var SheetInterface $sheet */
            foreach ($sheet->getRowIterator() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cells[] = trim((string) $cell->getValue());
                }
                $rows[] = $cells;
            }
            break;
        }
        $reader->close();

        return $this->rowsToPlanItems($rows);
    }

    /**
     * @param list<list<string|null>> $rows
     *
     * @return list<ParsedPlanRow>
     */
    private function rowsToPlanItems(array $rows): array
    {
        if ($rows === []) {
            throw new \InvalidArgumentException('Файл порожній.');
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->mapHeaders($headerRow ?? []);

        if (!isset($columnMap['title_uk'])) {
            throw new \InvalidArgumentException(
                'У першому рядку має бути заголовок з колонкою title_uk (або «назва» / title).',
            );
        }

        $parsed = [];
        $lineNumber = 1;
        foreach ($rows as $row) {
            ++$lineNumber;
            $title = $this->cell($row, $columnMap, 'title_uk');
            if ($title === '') {
                continue;
            }

            $category = $this->cell($row, $columnMap, 'category') ?: 'operations';
            $parsed[] = new ParsedPlanRow(
                lineNumber: $lineNumber,
                titleUk: $title,
                category: $category,
                intervalKm: $this->nullableInt($this->cell($row, $columnMap, 'interval_km')),
                intervalMonths: $this->nullableInt($this->cell($row, $columnMap, 'interval_months')),
                laborPriceUah: $this->nullableInt($this->cell($row, $columnMap, 'labor_price_uah')),
                notesUk: $this->nullableString($this->cell($row, $columnMap, 'notes_uk')),
                procedureCode: $this->nullableString($this->cell($row, $columnMap, 'procedure_code')),
            );
        }

        if ($parsed === []) {
            throw new \InvalidArgumentException('У файлі немає рядків з назвою роботи.');
        }

        return $parsed;
    }

    /**
     * @param list<string|null> $headerRow
     *
     * @return array<string, int>
     */
    private function mapHeaders(array $headerRow): array
    {
        $aliases = [
            'title_uk' => ['title_uk', 'title', 'назва', 'nazva', 'робота', 'name'],
            'category' => ['category', 'категорія', 'kategoriia', 'kategoria'],
            'interval_km' => ['interval_km', 'km', 'пробіг', 'interval km'],
            'interval_months' => ['interval_months', 'months', 'місяці', 'mis', 'interval months'],
            'labor_price_uah' => ['labor_price_uah', 'price', 'ціна', 'tsina', 'labor price'],
            'notes_uk' => ['notes_uk', 'notes', 'примітка', 'note', 'опис'],
            'procedure_code' => ['procedure_code', 'code', 'код'],
        ];

        $map = [];
        foreach ($headerRow as $index => $label) {
            $normalized = $this->normalizeHeader((string) $label);
            if ($normalized === '') {
                continue;
            }
            foreach ($aliases as $canonical => $options) {
                if (\in_array($normalized, $options, true)) {
                    $map[$canonical] = $index;
                }
            }
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace([' ', '-'], '_', $value);

        return $value;
    }

    /**
     * @param list<string|null> $row
     * @param array<string, int> $columnMap
     */
    private function cell(array $row, array $columnMap, string $key): string
    {
        if (!isset($columnMap[$key])) {
            return '';
        }

        return trim((string) ($row[$columnMap[$key]] ?? ''));
    }

    private function nullableInt(string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function nullableString(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
