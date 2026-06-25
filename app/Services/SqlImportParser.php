<?php

namespace App\Services;

/**
 * SqlImportParser
 *
 * Safely parses a MySQL dump file (.sql) containing one or more
 * INSERT INTO statements into an array of associative rows,
 * remapped to the BugTrack Laravel column names.
 *
 * Source table (`mfg_record.bug`) column mapping:
 *
 *   idbug             → id
 *   idproject         → project_id
 *   bug_title         → title
 *   severity          → severity         (unchanged)
 *   sn_code           → sn_code_snapshot
 *   id_sn             → serial_number_id
 *   tipe_pelapor      → reporter_type
 *   iddevice          → device_id
 *   bugdesc           → description
 *   bugversion        → product_version
 *   bugenvi           → environment
 *   bugreproduce      → reproduce_steps
 *   rootcause         → root_cause
 *   repair_action     → repair_action    (unchanged)
 *   is_rework         → is_rework        (unchanged)
 *   bugfile           → attachment_path
 *   bugexpected       → expected_result
 *   bugcreatedby      → reported_by
 *   bugstatus         → status
 *   bugfixby          → fixed_by
 *   bugclosesavedate  → closed_at
 *   created_at        → created_at       (unchanged)
 *   updated_at        → updated_at       (unchanged)
 *
 * Design goals:
 *  - Does NOT execute any SQL against the application database.
 *  - Does NOT assume a fixed table name — auto-detects from INSERT INTO.
 *  - Validates by column structure (source column names), NOT by table name.
 *  - Warns (but does not block) if table name doesn't contain "bug".
 *  - Handles standard mysqldump output: backtick-quoted identifiers,
 *    single-quoted string values with backslash escaping, NULL literals,
 *    integers, decimals, and datetime strings.
 */
class SqlImportParser
{
    /**
     * Canonical mapping from source column names → local BugTrack column names.
     * Keys must match exactly what appears in the INSERT INTO (...) column list.
     */
    private const COLUMN_MAP = [
        'idbug'            => 'id',
        'idproject'        => 'project_id',
        'bug_title'        => 'title',
        'severity'         => 'severity',
        'sn_code'          => 'sn_code_snapshot',
        'id_sn'            => 'serial_number_id',
        'tipe_pelapor'     => 'reporter_type',
        'iddevice'         => 'device_id',
        'bugdesc'          => 'description',
        'bugversion'       => 'product_version',
        'bugenvi'          => 'environment',
        'bugreproduce'     => 'reproduce_steps',
        'rootcause'        => 'root_cause',
        'repair_action'    => 'repair_action',
        'is_rework'        => 'is_rework',
        'bugfile'          => 'attachment_path',
        'bugexpected'      => 'expected_result',
        'bugcreatedby'     => 'reported_by',
        'bugstatus'        => 'status',
        'bugfixby'         => 'fixed_by',
        'bugclosesavedate' => 'closed_at',
        'created_at'       => 'created_at',
        'updated_at'       => 'updated_at',
    ];

    /**
     * Source column names that MUST be present for the INSERT block to be
     * recognized as a bug-table dump.  Checked against source names (pre-map).
     */
    private const REQUIRED_SOURCE_COLUMNS = [
        'idbug',
        'bug_title',
        'severity',
        'bugstatus',
    ];

    /**
     * Parse the content of a .sql dump file.
     *
     * @param  string  $sqlContent  Raw contents of the uploaded .sql file.
     * @return array{
     *   detected_table: string,
     *   table_looks_like_bug: bool,
     *   rows: array<int, array<string, mixed>>,
     *   parse_errors: string[]
     * }
     */
    public function parse(string $sqlContent): array
    {
        $result = [
            'detected_table'       => '',
            'table_looks_like_bug' => false,
            'rows'                 => [],
            'parse_errors'         => [],
        ];

        // ----------------------------------------------------------------
        // Step 1 – Find all INSERT INTO blocks in the file.
        // ----------------------------------------------------------------
        $insertPattern = '/INSERT\s+INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*([\s\S]+?);/i';

        if (!preg_match_all($insertPattern, $sqlContent, $insertMatches, PREG_SET_ORDER)) {
            $result['parse_errors'][] = 'Tidak ditemukan pernyataan INSERT INTO yang valid di dalam file .sql.';
            return $result;
        }

        // ----------------------------------------------------------------
        // Step 2 – Select the first INSERT block whose source columns pass
        //           the required-column check.
        // ----------------------------------------------------------------
        $targetSourceColumns = null;
        $targetTableName     = '';
        $targetValuesClauses = [];

        foreach ($insertMatches as $insertMatch) {
            $tableName = $insertMatch[1];
            $colRaw    = $insertMatch[2];
            $valuesRaw = $insertMatch[3];

            $sourceColumns = $this->parseColumnList($colRaw);

            if (!$this->sourceColumnsAreValid($sourceColumns)) {
                continue;
            }

            if ($targetSourceColumns === null) {
                $targetSourceColumns = $sourceColumns;
                $targetTableName     = $tableName;
            }

            if ($sourceColumns === $targetSourceColumns) {
                $targetValuesClauses[] = $valuesRaw;
            }
        }

        if ($targetSourceColumns === null) {
            $result['parse_errors'][] =
                'File .sql tidak mengandung INSERT INTO dengan kolom yang dikenali sebagai data bug '
                . '(kolom wajib: ' . implode(', ', self::REQUIRED_SOURCE_COLUMNS) . '). '
                . 'Pastikan file yang diupload adalah dump dari tabel bug.';
            return $result;
        }

        $result['detected_table']       = $targetTableName;
        $result['table_looks_like_bug'] = $this->tableNameLooksBug($targetTableName);

        // ----------------------------------------------------------------
        // Step 3 – Parse tuples and remap columns to local names.
        // ----------------------------------------------------------------
        foreach ($targetValuesClauses as $valuesClause) {
            $tuples = $this->splitValueTuples($valuesClause);

            foreach ($tuples as $tupleStr) {
                $values = $this->parseValueTuple($tupleStr, count($targetSourceColumns));

                if ($values === null) {
                    $result['parse_errors'][] = "Baris tidak dapat di-parse: " . substr($tupleStr, 0, 120);
                    continue;
                }

                if (count($values) !== count($targetSourceColumns)) {
                    $result['parse_errors'][] =
                        'Jumlah nilai (' . count($values) . ') tidak sesuai dengan jumlah kolom ('
                        . count($targetSourceColumns) . '): ' . substr($tupleStr, 0, 120);
                    continue;
                }

                // Combine source columns with values, then remap to local names.
                $rawRow    = array_combine($targetSourceColumns, $values);
                $mappedRow = $this->remapRow($rawRow);

                // Post-process status field: defensively normalize.
                if (isset($mappedRow['status'])) {
                    $mappedRow['status'] = $this->normalizeStatus($mappedRow['status']);
                }

                $result['rows'][] = $mappedRow;
            }
        }

        return $result;
    }

    // ====================================================================
    // Private helpers
    // ====================================================================

    /**
     * Parse the column-name list from an INSERT INTO clause.
     * Input: "`idbug`, `idproject`, `bug_title`"
     */
    private function parseColumnList(string $colRaw): array
    {
        $parts   = explode(',', $colRaw);
        $columns = [];
        foreach ($parts as $part) {
            $col = trim($part, " \t\r\n`'\"");
            if ($col !== '') {
                $columns[] = $col;
            }
        }
        return $columns;
    }

    /**
     * Check whether the detected source column list contains all required
     * source-side columns.
     */
    private function sourceColumnsAreValid(array $sourceColumns): bool
    {
        foreach (self::REQUIRED_SOURCE_COLUMNS as $required) {
            if (!in_array($required, $sourceColumns, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Heuristic: does the table name contain the word "bug"?
     */
    private function tableNameLooksBug(string $tableName): bool
    {
        return stripos($tableName, 'bug') !== false;
    }

    /**
     * Remap a raw associative row (keyed by source column names) to the
     * BugTrack local column names using COLUMN_MAP.
     * Unknown source columns that are not in the map are silently dropped.
     */
    private function remapRow(array $rawRow): array
    {
        $mapped = [];
        foreach ($rawRow as $sourceCol => $value) {
            $localCol = self::COLUMN_MAP[$sourceCol] ?? null;
            if ($localCol !== null) {
                $mapped[$localCol] = $value;
            }
            // Columns not in the map (e.g. unknown future columns) are dropped.
        }
        return $mapped;
    }

    /**
     * Defensively normalize a status value from the source (which is varchar,
     * not enum).  Rules:
     *  - Trim whitespace.
     *  - Uppercase for comparison.
     *  - If it clearly maps to OPEN or CLOSED → normalize.
     *  - Otherwise return as-is (the job layer will handle further validation).
     */
    private function normalizeStatus(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $upper = strtoupper(trim($value));
        return match ($upper) {
            'OPEN', 'BUKA', 'NEW'      => 'OPEN',
            'CLOSED', 'CLOSE', 'TUTUP',
            'SELESAI', 'DONE', 'FIXED' => 'CLOSED',
            default                     => $upper !== '' ? $upper : null,
        };
    }

    /**
     * Split a VALUES clause into individual tuple strings "(...)".
     * Walks character-by-character to handle commas inside quoted strings.
     */
    private function splitValueTuples(string $valuesClause): array
    {
        $tuples = [];
        $depth  = 0;
        $inStr  = false;
        $escape = false;
        $start  = null;
        $len    = strlen($valuesClause);

        for ($i = 0; $i < $len; $i++) {
            $ch = $valuesClause[$i];

            if ($escape) {
                $escape = false;
                continue;
            }
            if ($ch === '\\' && $inStr) {
                $escape = true;
                continue;
            }
            if ($ch === "'" && !$escape) {
                $inStr = !$inStr;
                continue;
            }
            if ($inStr) {
                continue;
            }

            if ($ch === '(') {
                if ($depth === 0) {
                    $start = $i;
                }
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
                if ($depth === 0 && $start !== null) {
                    $tuples[] = substr($valuesClause, $start, $i - $start + 1);
                    $start    = null;
                }
            }
        }

        return $tuples;
    }

    /**
     * Parse a single row tuple "(v1, v2, 'str', NULL, ...)" into a PHP array.
     * Returns null if the tuple cannot be parsed at all.
     *
     * @return array<mixed>|null
     */
    private function parseValueTuple(string $tupleStr, int $colCount): ?array
    {
        $inner  = substr(trim($tupleStr), 1, -1); // strip outer ()
        $values = [];
        $len    = strlen($inner);
        $i      = 0;

        while ($i < $len) {
            // Skip leading whitespace.
            while ($i < $len && ctype_space($inner[$i])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }

            $ch = $inner[$i];

            if ($ch === "'") {
                // ---- String value ----------------------------------------
                $i++;
                $buf = '';
                while ($i < $len) {
                    $c = $inner[$i];
                    if ($c === '\\') {
                        $i++;
                        if ($i < $len) {
                            $esc = $inner[$i];
                            $buf .= match ($esc) {
                                'n'  => "\n",
                                'r'  => "\r",
                                't'  => "\t",
                                '0'  => "\0",
                                '\\' => '\\',
                                "'"  => "'",
                                '"'  => '"',
                                default => $esc,
                            };
                            $i++;
                        }
                        continue;
                    }
                    if ($c === "'") {
                        // SQL-standard doubled single-quote escape ('').
                        if ($i + 1 < $len && $inner[$i + 1] === "'") {
                            $buf .= "'";
                            $i += 2;
                            continue;
                        }
                        $i++;
                        break;
                    }
                    $buf .= $c;
                    $i++;
                }
                $values[] = $buf;

            } elseif (strtoupper(substr($inner, $i, 4)) === 'NULL') {
                // ---- NULL value ------------------------------------------
                $values[] = null;
                $i += 4;

            } else {
                // ---- Numeric or bare value --------------------------------
                $end = $i;
                while ($end < $len && $inner[$end] !== ',' && $inner[$end] !== ')') {
                    $end++;
                }
                $raw = trim(substr($inner, $i, $end - $i));
                if (is_numeric($raw)) {
                    $values[] = strpos($raw, '.') !== false ? (float) $raw : (int) $raw;
                } else {
                    $values[] = $raw !== '' ? $raw : null;
                }
                $i = $end;
            }

            // Skip separator comma.
            while ($i < $len && ($inner[$i] === ',' || ctype_space($inner[$i]))) {
                $i++;
            }
        }

        return count($values) > 0 ? $values : null;
    }
}
