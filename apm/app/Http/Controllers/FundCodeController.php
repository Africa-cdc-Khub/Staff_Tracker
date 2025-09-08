<?php

namespace App\Http\Controllers;

use App\Models\FundCode;
use App\Models\FundType;
use App\Models\Division;
use App\Models\Funder;
use Illuminate\Http\Request;
use App\Models\FundCodeTransaction;

class FundCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FundCode::query()->with(['fundType', 'division', 'funder']);
        
        // Default to current year if no year filter is provided
        $currentYear = date('Y');
        $year = $request->input('year', $currentYear);
        $query->where('year', $year);
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('activity', 'like', "%{$search}%")
                  ->orWhere('cost_centre', 'like', "%{$search}%")
                  ->orWhereHas('funder', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('fundType', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('division', function($q) use ($search) {
                      $q->where('division_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply fund type filter if provided
        if ($request->has('fund_type_id') && !empty($request->fund_type_id)) {
            $query->where('fund_type_id', $request->fund_type_id);
        }
        
        // Apply division filter if provided
        if ($request->has('division_id') && !empty($request->division_id)) {
            $query->where('division_id', $request->division_id);
        }
        
        // Apply status filter if provided
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $fundCodes = $query->orderBy('code')->paginate(10);
        $fundTypes = FundType::orderBy('name')->get();
        $divisions = Division::orderBy('division_name')->get();
        $funders = Funder::orderBy('name')->get();
        
        return view('fund-codes.index', compact('fundCodes', 'fundTypes', 'divisions', 'funders', 'year'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $fundTypes = FundType::orderBy('name')->get();
        $divisions = Division::orderBy('division_name')->get();
        $selectedFundType = $request->input('fund_type_id');
        $funders = Funder::orderBy('name')->get();
        
        return view('fund-codes.create', compact('fundTypes', 'divisions', 'selectedFundType', 'funders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'funder_id' => 'nullable|exists:funders,id',
            'year' => 'required|integer|min:2000|max:2100',
            'code' => 'required|string|max:255|unique:fund_codes',
            'activity' => 'nullable|string',
            'fund_type_id' => 'nullable|exists:fund_types,id',
            'division_id' => 'nullable|exists:divisions,id',
            'cost_centre' => 'nullable|string|max:255',
            'amert_code' => 'nullable|string|max:255',
            'fund' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'budget_balance' => 'nullable|string|max:255',
            'approved_budget' => 'nullable|string|max:255',
            'uploaded_budget' => 'nullable|string|max:255',
        ]);

        // Custom validation: Division is required only for intramural fund types
        if ($request->fund_type_id) {
            $fundType = FundType::find($request->fund_type_id);
            if ($fundType && strtolower($fundType->name) === 'intramural' && empty($request->division_id)) {
                return back()->withErrors(['division_id' => 'Division is required for intramural fund types.'])->withInput();
            }
        }

        // Set is_active to true by default if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $fundCode = FundCode::create($validated);

        return redirect()->route('fund-codes.index')
            ->with('msg', 'Fund Code created successfully.')
            ->with('type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(FundCode $fundCode)
    {
        $fundCode->load(['fundType', 'division', 'funder']);
        return view('fund-codes.show', compact('fundCode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FundCode $fundCode)
    {
        $fundTypes = FundType::orderBy('name')->get();
        $divisions = Division::orderBy('division_name')->get();
        $funders = \App\Models\Funder::orderBy('name')->get();
        
        return view('fund-codes.edit', compact('fundCode', 'fundTypes', 'divisions', 'funders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FundCode $fundCode)
    {
        $validated = $request->validate([
            'funder_id' => 'nullable|exists:funders,id',
            'year' => 'required|integer|min:2000|max:2100',
            'code' => 'required|string|max:255|unique:fund_codes,code,' . $fundCode->id,
            'activity' => 'nullable|string',
            'fund_type_id' => 'nullable|exists:fund_types,id',
            'division_id' => 'nullable|exists:divisions,id',
            'cost_centre' => 'nullable|string|max:255',
            'amert_code' => 'nullable|string|max:255',
            'fund' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'budget_balance' => 'nullable|string|max:255',
            'approved_budget' => 'nullable|string|max:255',
            'uploaded_budget' => 'nullable|string|max:255',
        ]);

        // Custom validation: Division is required only for intramural fund types
        if ($request->fund_type_id) {
            $fundType = FundType::find($request->fund_type_id);
            if ($fundType && strtolower($fundType->name) === 'intramural' && empty($request->division_id)) {
                return back()->withErrors(['division_id' => 'Division is required for intramural fund types.'])->withInput();
            }
        }

        // Handle checkbox for is_active
        $validated['is_active'] = $request->has('is_active');

        $fundCode->update($validated);

        return redirect()->route('fund-codes.index')
            ->with('msg', 'Fund Code updated successfully.')
            ->with('type', 'success');
    }


    public function transactions(Request $request, FundCode $fundCode)
    {
        $query = FundCodeTransaction::where('fund_code_id', $fundCode->id)
            ->with(['activity', 'matrix', 'createdBy']);

        // Apply date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply amount range filter
        if ($request->has('amount_from') && !empty($request->amount_from)) {
            $query->where('amount', '>=', $request->amount_from);
        }
        
        if ($request->has('amount_to') && !empty($request->amount_to)) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('activity', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('matrix', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('createdBy', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply transaction type filter
        if ($request->has('transaction_type') && !empty($request->transaction_type)) {
            if ($request->transaction_type === 'credit') {
                $query->where('amount', '>', 0);
            } elseif ($request->transaction_type === 'debit') {
                $query->where('amount', '<', 0);
            }
        }

        // Apply reversal filter
        if ($request->has('is_reversal')) {
            $query->where('is_reversal', $request->is_reversal);
        }

        // Handle export
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportTransactions($query->orderBy('created_at', 'desc')->get(), $fundCode);
        }

        $fundCodeTransactions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('fund-codes.transactions', compact('fundCodeTransactions', 'fundCode'));
    }

    private function exportTransactions($transactions, $fundCode)
    {
        $filename = 'fund_code_' . $fundCode->code . '_transactions_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Description', 
                'Amount',
                'Balance Before',
                'Balance After',
                'Activity',
                'Matrix',
                'Created By',
                'Transaction Type',
                'Is Reversal'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->description,
                    $transaction->amount,
                    $transaction->balance_before,
                    $transaction->balance_after,
                    $transaction->activity->name ?? 'N/A',
                    $transaction->matrix->name ?? 'N/A',
                    $transaction->createdBy->name ?? 'N/A',
                    $transaction->amount > 0 ? 'Credit' : 'Debit',
                    $transaction->is_reversal ? 'Yes' : 'No'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
        ]);

        $file = $request->file('csv_file');
        $skipDuplicates = $request->has('skip_duplicates');
        $validateOnly = $request->has('validate_only');

        try {
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $headers = array_shift($csvData); // Remove header row
            
            // Validate headers
            $requiredHeaders = ['funder_id', 'year', 'code', 'fund_type_id'];
            $optionalHeaders = ['activity', 'division_id', 'cost_centre', 'amert_code', 'fund', 'budget_balance', 'approved_budget', 'uploaded_budget', 'is_active'];
            $allValidHeaders = array_merge($requiredHeaders, $optionalHeaders);
            
            $missingHeaders = array_diff($requiredHeaders, $headers);
            if (!empty($missingHeaders)) {
                return back()->withErrors(['csv_file' => 'Missing required headers: ' . implode(', ', $missingHeaders)]);
            }

            $errors = [];
            $successCount = 0;
            $skippedCount = 0;
            $processedRows = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
                
                if (count($row) !== count($headers)) {
                    $errors[] = "Row {$rowNumber}: Column count mismatch";
                    continue;
                }

                $rowData = array_combine($headers, $row);
                
                // Validate required fields
                foreach ($requiredHeaders as $header) {
                    if (empty($rowData[$header])) {
                        $errors[] = "Row {$rowNumber}: {$header} is required";
                    }
                }

                if (!empty($errors) && count($errors) > 10) {
                    $errors[] = "Too many errors. Stopping validation.";
                    break;
                }

                // Validate funder exists
                if (!empty($rowData['funder_id']) && !Funder::find($rowData['funder_id'])) {
                    $errors[] = "Row {$rowNumber}: Invalid funder_id";
                }

                // Validate fund type exists
                if (!empty($rowData['fund_type_id']) && !FundType::find($rowData['fund_type_id'])) {
                    $errors[] = "Row {$rowNumber}: Invalid fund_type_id";
                }

                // Validate division exists
                if (!empty($rowData['division_id']) && !Division::find($rowData['division_id'])) {
                    $errors[] = "Row {$rowNumber}: Invalid division_id";
                }

                // Check for division requirement for intramural
                if (!empty($rowData['fund_type_id'])) {
                    $fundType = FundType::find($rowData['fund_type_id']);
                    if ($fundType && strtolower($fundType->name) === 'intramural' && empty($rowData['division_id'])) {
                        $errors[] = "Row {$rowNumber}: Division is required for intramural fund types";
                    }
                }

                // Check for duplicates
                if (!$skipDuplicates && !empty($rowData['code']) && !empty($rowData['year'])) {
                    $existing = FundCode::where('code', $rowData['code'])
                                      ->where('year', $rowData['year'])
                                      ->first();
                    if ($existing) {
                        $errors[] = "Row {$rowNumber}: Fund code '{$rowData['code']}' for year {$rowData['year']} already exists";
                    }
                }

                if (empty($errors) || count($errors) <= 10) {
                    $processedRows[] = $rowData;
                }
            }

            if (!empty($errors)) {
                return back()->withErrors(['csv_file' => implode('; ', array_slice($errors, 0, 10))]);
            }

            if ($validateOnly) {
                return back()->with('msg', 'CSV validation successful. ' . count($processedRows) . ' rows are valid.')
                            ->with('type', 'success');
            }

            // Process valid rows
            foreach ($processedRows as $rowData) {
                // Check for duplicates again if skip_duplicates is enabled
                if ($skipDuplicates && !empty($rowData['code']) && !empty($rowData['year'])) {
                    $existing = FundCode::where('code', $rowData['code'])
                                      ->where('year', $rowData['year'])
                                      ->first();
                    if ($existing) {
                        $skippedCount++;
                        continue;
                    }
                }

                // Set default values
                $rowData['is_active'] = isset($rowData['is_active']) ? (strtolower($rowData['is_active']) === 'true' || $rowData['is_active'] === '1') : true;
                
                // Remove empty values
                $rowData = array_filter($rowData, function($value) {
                    return $value !== '' && $value !== null;
                });

                FundCode::create($rowData);
                $successCount++;
            }

            $message = "Import completed successfully. ";
            $message .= "Imported: {$successCount} records";
            if ($skippedCount > 0) {
                $message .= ", Skipped: {$skippedCount} duplicates";
            }

            return redirect()->route('fund-codes.index')
                ->with('msg', $message)
                ->with('type', 'success');

        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Error processing CSV: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $filename = 'fund_codes_template_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'funder_id',
                'year', 
                'code',
                'fund_type_id',
                'activity',
                'division_id',
                'cost_centre',
                'amert_code',
                'fund',
                'budget_balance',
                'approved_budget',
                'uploaded_budget',
                'is_active'
            ]);

            // Sample data row
            fputcsv($file, [
                '1', // funder_id (example)
                '2025', // year
                'AF-2025-01', // code
                '1', // fund_type_id (example)
                'Sample Activity', // activity
                '1', // division_id (example, required for intramural)
                'CC001', // cost_centre
                'AM001', // amert_code
                'FUND001', // fund
                '10000.00', // budget_balance
                '10000.00', // approved_budget
                '10000.00', // uploaded_budget
                'true' // is_active
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
