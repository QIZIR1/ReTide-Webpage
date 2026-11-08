<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationUpdate;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;

class DonationUpdateController extends Controller
{
    public function index(Donation $donation)
    {
        $updates = DonationUpdate::where('donation_id', $donation->id)->latest()->get();

        if (request()->ajax()) {
            return view('admin.donation_updates.partials.updates_list', compact('updates'));
        }

        return view('admin.donation_updates.index', compact('donation', 'updates'));
    }

    public function create(Donation $donation)
    {
        return view('admin.donation_updates.create', compact('donation'));
    }

    public function store(Request $request, Donation $donation)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
        ]);

        $donationUpdate = DonationUpdate::create([
            'donation_id' => $donation->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        SecurityAuditService::log('ADMIN_DONATION_STATUS_UPDATE', [
            'donation_id' => $donation->id,
            'update_id' => $donationUpdate->id,
            'status' => $request->status,
            'title' => $request->title,
        ]);

        return redirect()
            ->route('admin.donations.updates.index', $donation->id)
            ->with('success', 'Update donasi berhasil ditambahkan');
    }
}
