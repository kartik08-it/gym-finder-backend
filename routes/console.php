<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('gym:expire-memberships', function () {
    $now = now()->toDateString();
    \App\Models\MembershipHistory::where('status', 'active')
        ->where('ends_on', '<', $now)
        ->update(['status' => 'expired']);
    \App\Models\Booking::where('status', 'confirmed')
        ->where('ends_on', '<', $now)
        ->update(['status' => 'expired']);
    $this->info('Expired memberships marked.');
})->purpose('Mark expired memberships/bookings.')
  ->daily();
