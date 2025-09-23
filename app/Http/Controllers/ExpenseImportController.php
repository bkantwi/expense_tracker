<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExpenseImportController extends Controller
{
    public function index()
    {
        return view('imports.expenses.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file'        => ['required','file','mimes:csv,txt','max:2048'],
            'delimiter'   => [Rule::in([',',';','tab'])], // ✅ allow comma, semicolon, or 'tab'
            'has_header'  => ['nullable','boolean'],
        ]);

        $delimiter = $request->input('delimiter', ',');
        if ($delimiter === 'tab') $delimiter = "\t";
        $hasHeader = (bool) $request->boolean('has_header', true);

        // Parse CSV (first up to 1000 rows)
        $fh = fopen($request->file('file')->getRealPath(), 'r');
        if ($fh === false) {
            return back()->withErrors(['file' => 'Could not read file.']);
        }

        $rows = [];
        $header = null;
        $count = 0;

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
            // normalize BOM / utf8
            if ($count === 0 && isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
            }

            if ($count === 0 && $hasHeader) {
                $header = $row;
            } else {
                $rows[] = $row;
            }

            $count++;
            if (count($rows) >= 1000) break;
        }
        fclose($fh);

        // Build column labels
        if (!$header) {
            $maxCols = 0;
            foreach ($rows as $r) $maxCols = max($maxCols, count($r));
            $header = array_map(fn($i) => 'Column '.$i, range(1, $maxCols));
        }

        // Guess mapping from header names
        $guess = $this->guessMapping($header);

        // Save parsed rows in session for commit
        $payload = [
            'header'    => $header,
            'rows'      => $rows,
            'hasHeader' => $hasHeader,
        ];
        session(['import.expenses' => $payload]);

        return view('imports.expenses.preview', [
            'header'   => $header,
            'rows'     => array_slice($rows, 0, 50), // show first 50
            'guess'    => $guess,
            'dateFormats' => ['Y-m-d','d/m/Y','m/d/Y','d-m-Y','M d, Y'],
        ]);
    }

    public function commit(Request $request)
    {
        $payload = session('import.expenses');
        if (!$payload || !is_array($payload)) {
            return redirect()->route('expenses.import.index')
                ->withErrors(['file' => 'Import session expired. Please upload the CSV again.']);
        }

        $request->validate([
            'map.date'     => ['required','integer','min:0'],
            'map.title'    => ['required','integer','min:0'],
            'map.amount'   => ['required','integer','min:0'],
            'map.category' => ['nullable','integer','min:0'],
            'map.notes'    => ['nullable','integer','min:0'],
            'date_format'  => ['required','string'],
            'create_categories' => ['nullable','boolean'],
            'invert_sign'  => ['nullable','boolean'],
        ]);

        $map   = $request->input('map');
        $fmt   = $request->string('date_format');
        $make  = $request->boolean('create_categories', true);
        $invert= $request->boolean('invert_sign', false);

        $rows  = $payload['rows'] ?? [];
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        $userId = Auth::id();

        // Ensure we have an "Uncategorized" fallback if no category provided
        $fallbackCategory = Category::firstOrCreate(
            ['user_id' => $userId, 'name' => 'Uncategorized'],
            []
        );

        foreach ($rows as $i => $row) {
            try {
                $dateStr = self::getCell($row, (int)$map['date']);
                $title   = trim((string) self::getCell($row, (int)$map['title']));
                $amountR = (string) self::getCell($row, (int)$map['amount']);
                $catName = isset($map['category']) ? trim((string) self::getCell($row, (int)$map['category'])) : '';
                $notes   = isset($map['notes']) ? trim((string) self::getCell($row, (int)$map['notes'])) : null;

                if ($title === '') $title = 'Imported expense';

                // Parse date (trim + tolerant fallback)
                $dateStr = trim((string) self::getCell($row, (int)$map['date']));
                if ($dateStr === '') {
                    throw new \RuntimeException('Missing date');
                }

                try {
                    $date = \Illuminate\Support\Carbon::createFromFormat($fmt, $dateStr);
                } catch (\Throwable $e) {
                    // normalize common separators and try a generic parse
                    $try = preg_replace('/[\.\/]/', '-', $dateStr);
                    $date = \Illuminate\Support\Carbon::parse($try);
                }
                $date = $date->toDateString();

                // Parse amount: handle commas, spaces, parentheses
                $amount = $this->parseAmount($amountR, $invert);
                if ($amount <= 0) {
                    // treat zero/negative after normalization as skip
                    $skipped++;
                    continue;
                }

                // Find or create category
                $category = $fallbackCategory;
                if ($catName !== '') {
                    $category = Category::where('user_id', $userId)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($catName)])
                        ->first();

                    if (!$category && $make) {
                        $category = Category::create(['user_id' => $userId, 'name' => $catName]);
                    }
                    if (!$category) $category = $fallbackCategory;
                }

                // Duplicate guard: same user + date + title + amount
                $exists = Expense::where('user_id', $userId)
                    ->whereDate('spent_at', $date)
                    ->where('title', $title)
                    ->where('amount', round($amount, 2))
                    ->exists();

                if ($exists) { $skipped++; continue; }

                Expense::create([
                    'user_id'     => $userId,
                    'category_id' => $category->id,
                    'title'       => $title,
                    'amount'      => round($amount, 2),
                    'spent_at'    => $date,
                    'notes'       => $notes,
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                // collect a short error per row
                $errors[] = 'Row '.($i+1).': '.$e->getMessage();
                // continue with remaining rows
            }
        }

        // clear session payload
        session()->forget('import.expenses');

        return redirect()->route('expenses.index')
            ->with('success', "Imported: {$imported}, Skipped: {$skipped}")
            ->with('import_errors', $errors);
    }

    private static function getCell(array $row, int $idx): ?string
    {
        return array_key_exists($idx, $row) ? (string)$row[$idx] : null;
    }

    private function guessMapping(array $header): array
    {
        $map = ['date'=>0,'title'=>0,'amount'=>0,'category'=>null,'notes'=>null];

        foreach ($header as $i => $h) {
            $k = mb_strtolower(trim((string)$h));
            if (Str::contains($k, ['date','posted','transaction date'])) $map['date'] = $i;
            if (Str::contains($k, ['title','description','details','narration'])) $map['title'] = $i;
            if (Str::contains($k, ['amount','debit','value'])) $map['amount'] = $i;
            if (Str::contains($k, ['category','tag'])) $map['category'] = $i;
            if (Str::contains($k, ['note','memo','remark'])) $map['notes'] = $i;
        }

        return $map;
    }

    private function parseAmount(string $raw, bool $invert): float
    {
        $s = trim($raw);
        // handle parentheses for negatives "(123.45)"
        $negative = false;
        if (Str::startsWith($s, '(') && Str::endsWith($s, ')')) {
            $negative = true;
            $s = trim($s, '()');
        }
        // remove thousand separators and spaces
        $s = str_replace([' ', ',', "\xc2\xa0"], ['', '', ''], $s);
        // normalize decimal comma (e.g., "1234,56")
        if (preg_match('/^\d+\.\d{1,2}$/', $s) === 0 && preg_match('/^\d+,\d{1,2}$/', $s) === 1) {
            $s = str_replace(',', '.', $s);
        }
        $num = (float) $s;
        if ($negative) $num *= -1;
        if ($invert)  $num *= -1; // some exports use negative for expenses; invert to positive

        return abs($num);
    }
}
