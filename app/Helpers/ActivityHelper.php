<?php

namespace App\Helpers;

use App\Models\Complain;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class ActivityHelper
{
    /**
     * Get recent society activities across all modules.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getRecentActivities($limit = 8)
    {
        try {
            $maintenanceBillModel = ModuleHelper::getModel('MaintenanceBill');
            $isFinanceActive = ModuleHelper::isFinanceActive() && ModuleHelper::hasModel($maintenanceBillModel, 'maintenance_bills');

            $recentPayments = collect([]);
            if ($isFinanceActive && $maintenanceBillModel) {
                $recentPayments = $maintenanceBillModel::with('flat.block', 'user')
                    ->where('status', 'paid')
                    ->latest('updated_at')
                    ->get()
                    ->groupBy(function ($bill) {
                        $timeKey = $bill->paid_at ? $bill->paid_at->format('Y-m-d_H:i') : $bill->updated_at->format('Y-m-d_H:i');
                        return 'user_' . $bill->user_id . '_flat_' . $bill->flat_id . '_' . $timeKey;
                    })
                    ->take(4)
                    ->map(function ($billsGroup) {
                        $bill = $billsGroup->first();
                        $totalAmount = $billsGroup->sum('total_amount');
                        $monthsCount = $billsGroup->count();
                        $residentName = $bill->user?->name ?? 'Unknown Resident';
                        $flatNo = ($bill->block ? $bill->block->block_name . '-' : '') . ($bill->flat?->flat_no ?? 'N/A');
                        $durationText = $monthsCount > 1 ? " ({$monthsCount} months)" : "";

                        $paymentMethod = strtolower($bill->payment_method ?? '');

                        if (in_array($paymentMethod, ['upi', 'online', 'card', 'netbanking'], true)) {
                            $badgeText = 'Paid Online';
                            $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                        } elseif ($paymentMethod === 'cash') {
                            $badgeText = 'Paid Cash';
                            $badgeClass = 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                        } else {
                            $badgeText = $paymentMethod ? ucfirst($paymentMethod) : 'Paid';
                            $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                        }

                        return (object) [
                            'type' => 'payment',
                            'icon' => 'fa-solid fa-money-bill-wave text-success fs-6',
                            'bg_class' => 'bg-success bg-opacity-10 text-success',
                            'title' => 'Payment Received',
                            'description' => "{$residentName} (Flat #{$flatNo}) paid " . CurrencyHelper::formatCurrency($totalAmount) . $durationText,
                            'time' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now())->diffForHumans(),
                            'timestamp' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now()),
                            'url' => Route::has('maintenance-bills.index') ? route('maintenance-bills.index') : '#',
                            'badge_text' => $badgeText,
                            'badge_class' => $badgeClass
                        ];
                    })
                    ->values();
            }

            $recentComplaints = Complain::with('user')
                ->latest('created_at')
                ->take(4)
                ->get()
                ->map(function ($complain) {
                    $userName = $complain->user?->name ?? 'Resident';
                    return (object) [
                        'type' => 'complain',
                        'icon' => 'fa-solid fa-triangle-exclamation text-danger fs-6',
                        'bg_class' => 'bg-danger bg-opacity-10 text-danger',
                        'title' => 'New Complaint Logged',
                        'description' => "{$userName}: \"{$complain->subject}\"",
                        'time' => Carbon::parse($complain->created_at ?? $complain->updated_at ?? now())->diffForHumans(),
                        'timestamp' => Carbon::parse($complain->created_at ?? $complain->updated_at ?? now()),
                        'url' => Route::has('complains.index') ? route('complains.index') : '#',
                        'badge_text' => ucfirst($complain->status ?? 'Pending'),
                        'badge_class' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25'
                    ];
                });

            $unapprovedTransferUserIds = [];
            $recentTransfers = collect([]);
            $nameTransferModel = ModuleHelper::getModel('NameTransferBill');
            if ($isFinanceActive && $nameTransferModel && ModuleHelper::hasModel($nameTransferModel, 'name_transfer_bills')) {
                $unapprovedTransferUserIds = $nameTransferModel::where(function ($q) {
                    $q->where('is_approved', false)->orWhereNull('is_approved');
                })->pluck('new_owner_id')->filter()->toArray();

                $recentTransfers = $nameTransferModel::with('flat.block', 'oldOwner', 'newOwner')
                    ->where('is_approved', true)
                    ->latest('updated_at')
                    ->take(4)
                    ->get()
                    ->map(function ($transfer) {
                        $oldName = $transfer->oldOwner?->name ?? 'Previous Owner';
                        $newName = $transfer->newOwner?->name ?? 'New Owner';
                        $flatNo = ($transfer->flat?->block ? $transfer->flat->block->block_name . '-' : '') . ($transfer->flat?->flat_no ?? 'N/A');

                        return (object) [
                            'type' => 'transfer',
                            'icon' => 'fa-solid fa-right-left text-info fs-6',
                            'bg_class' => 'bg-info bg-opacity-10 text-info',
                            'title' => 'Ownership Transferred',
                            'description' => "Flat #{$flatNo} transferred from {$oldName} to {$newName}",
                            'time' => Carbon::parse($transfer->updated_at ?? $transfer->created_at ?? now())->diffForHumans(),
                            'timestamp' => Carbon::parse($transfer->updated_at ?? $transfer->created_at ?? now()),
                            'url' => Route::has('name-transfer-bills.index') ? route('name-transfer-bills.index') : '#',
                            'badge_text' => 'Transfer',
                            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25'
                        ];
                    });
            }

            $recentUsers = User::whereNotIn('id', $unapprovedTransferUserIds)
                ->latest('updated_at')
                ->take(3)
                ->get()
                ->map(function ($user) {
                    $roleLabel = ucfirst($user->role);
                    return (object) [
                        'type' => 'user',
                        'icon' => 'fa-solid fa-user-plus text-primary fs-6',
                        'bg_class' => 'bg-primary bg-opacity-10 text-primary',
                        'title' => 'New Resident Registered',
                        'description' => "{$user->name} joined as {$roleLabel}",
                        'time' => Carbon::parse($user->updated_at ?? $user->created_at ?? now())->diffForHumans(),
                        'timestamp' => Carbon::parse($user->updated_at ?? $user->created_at ?? now()),
                        'url' => Route::has('residents.index') ? route('residents.index') : '#',
                        'badge_text' => 'New Member',
                        'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25'
                    ];
                });

            return $recentPayments->concat($recentComplaints)->concat($recentUsers)->concat($recentTransfers)
                ->sortByDesc('timestamp')
                ->take($limit)
                ->values();
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}
