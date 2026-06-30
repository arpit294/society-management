<?php

namespace App\Http\Requests;

use App\Models\MaintenanceBill;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMaintenanceBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! in_array(strtolower((string) $this->input('payment_method')), ['upi'], true)) {
            $this->merge(['transaction_id' => null]);

            return;
        }

        if ($this->has('transaction_id')) {
            $this->merge([
                'transaction_id' => trim((string) $this->input('transaction_id')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resident_id' => 'required|exists:residents,id',
            'months' => 'required|integer|min:1|max:12',
            'start_month' => 'required|string',
            'start_year' => 'required|integer',
            'payment_method' => 'required|in:cash,upi,CASH,UPI',
            'transaction_id' => [
                'nullable',
                'required_if:payment_method,upi,UPI',
                'digits:12',
                Rule::unique('maintenance_bills', 'transaction_id'),
                Rule::unique('name_transfer_bills', 'transaction_id'),
                Rule::unique('prepaid_maintenances', 'transaction_id'),
            ],
            'payment_slip' => 'required_if:payment_method,upi,UPI|image|mimes:jpeg,png,jpg|max:2048',
            'discount_amount' => 'nullable|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.required_if' => 'The UTR number is required for UPI payments.',
            'transaction_id.digits' => 'The UTR number must be exactly 12 digits.',
            'transaction_id.unique' => 'This UTR number has already been used.',
        ];
    }

    public function attributes(): array
    {
        return [
            'transaction_id' => 'UTR number',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $residentId = $this->input('resident_id');
            $startMonth = $this->input('start_month');
            $startYear = $this->input('start_year');
            $months = $this->input('months');

            if ($residentId && $startMonth && $startYear && $months) {
                $resident = Resident::find($residentId);

                if ($resident) {
                    try {
                        $currentDate = Carbon::createFromDate($startYear, Carbon::parse($startMonth)->month, 1);

                        for ($i = 0; $i < $months; $i++) {
                            $loopDate = $currentDate->copy()->addMonths($i);
                            $monthStr = $loopDate->format('F');
                            $yearInt = $loopDate->year;

                            $isPaid = MaintenanceBill::where('maintenance_bills.user_id', $resident->user_id)
                                ->where('maintenance_bills.flat_id', $resident->flat_id)
                                ->where('maintenance_bills.status', 'paid')
                                ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                                ->where('maintenances.month', $monthStr)
                                ->where('maintenances.year', $yearInt)
                                ->exists();

                            if ($isPaid) {
                                throw new HttpResponseException(
                                    response()->json(['message' => "Maintenance for {$monthStr} {$yearInt} is already paid."], 422)
                                );
                            }
                        }
                    } catch (HttpResponseException $e) {
                        throw $e;
                    } catch (\Exception $e) {
                        // Ignore date parsing errors here, standard validation will catch them
                    }
                }
            }
        });
    }
}
