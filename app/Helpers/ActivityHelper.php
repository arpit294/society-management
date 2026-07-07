<?php

namespace App\Helpers;

use App\Models\MaintenanceBill;
use App\Models\Complain;
use App\Models\User;
use App\Models\NameTransferBill;

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
            $recentPayments = MaintenanceBill::with('flat.block', 'user')
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

                    return (object) [
                        'type' => 'payment',
                        'icon' => 'fa-solid fa-money-bill-wave text-success fs-6',
                        'bg_class' => 'bg-success bg-opacity-10 text-success',
                        'title' => 'Payment Received',
                        'description' => "{$residentName} (Flat #{$flatNo}) paid " . CurrencyHelper::formatCurrency($totalAmount) . $durationText,
                        'time' => $bill->updated_at->diffForHumans(),
                        'timestamp' => $bill->updated_at,
                        'url' => route('maintenance-bills.index'),
                        'badge_text' => 'Paid Online',
                        'badge_class' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'
                    ];
                })
                ->values();

            $recentComplaints = Complain::with('user')
                ->latest('created_at')
                ->take(4)
                ->get()
                ->map(function ($complain) {
                    $userName = $complain->user?->name ?? 'Resident';
                    return (object) [
                        'type' => 'complain',
                        'icon' => 'fa-solid fa-screwdriver-wrench text-warning fs-6',
                        'bg_class' => 'bg-warning bg-opacity-10 text-warning',
                        'title' => 'New Complaint Logged',
                        'description' => "{$userName}: \"{$complain->subject}\"",
                        'time' => $complain->created_at->diffForHumans(),
                        'timestamp' => $complain->created_at,
                        'url' => route('complains.index'),
                        'badge_text' => 'Pending Review',
                        'badge_class' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25'
                    ];
                });

            $recentUsers = User::latest('created_at')
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
                        'time' => $user->created_at->diffForHumans(),
                        'timestamp' => $user->created_at,
                        'url' => route('residents.index'),
                        'badge_text' => 'New Member',
                        'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25'
                    ];
                });

            $recentTransfers = NameTransferBill::with('flat.block', 'oldOwner', 'newOwner')
                ->latest('created_at')
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
                        'time' => $transfer->created_at->diffForHumans(),
                        'timestamp' => $transfer->created_at,
                        'url' => route('name-transfer-bills.index'),
                        'badge_text' => 'Transfer',
                        'badge_class' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25'
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
