<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'finance_vendors';

    protected $fillable = [
        'name',
        'service_type',
        'contact_person',
        'phone',
        'email',
        'gstin',
        'pan_number',
        'bank_name',
        'bank_account_no',
        'bank_ifsc',
        'address',
        'status',
    ];

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class, 'vendor_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'vendor_id');
    }
}
