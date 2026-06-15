<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'transaction_id' => 'nullable|string',
            'payment_slip' => 'required_if:payment_method,upi,UPI|image|mimes:jpeg,png,jpg|max:2048',
            'discount_amount' => 'nullable|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
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
                $resident = \App\Models\Resident::find($residentId);
                
                if ($resident) {
                    try {
                        $currentDate = \Carbon\Carbon::createFromDate($startYear, \Carbon\Carbon::parse($startMonth)->month, 1);
                        
                        for ($i = 0; $i < $months; $i++) {
                            $loopDate = $currentDate->copy()->addMonths($i);
                            $monthStr = $loopDate->format('F');
                            $yearInt = $loopDate->year;
                            
                            $isPaid = \App\Models\MaintenanceBill::where('maintenance_bills.user_id', $resident->user_id)
                                ->where('maintenance_bills.flat_id', $resident->flat_id)
                                ->where('maintenance_bills.status', 'paid')
                                ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                                ->where('maintenances.month', $monthStr)
                                ->where('maintenances.year', $yearInt)
                                ->exists();

                            if ($isPaid) {
                                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                                    response()->json(['message' => "Maintenance for {$monthStr} {$yearInt} is already paid."], 422)
                                );
                            }
                        }
                    } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
                        throw $e;
                    } catch (\Exception $e) {
                        // Ignore date parsing errors here, standard validation will catch them
                    }
                }
            }
        });
    }
}
