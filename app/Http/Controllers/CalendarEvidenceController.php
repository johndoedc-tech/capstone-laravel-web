<?php

namespace App\Http\Controllers;

use App\Models\FarmerCalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CalendarEvidenceController extends Controller
{
    public function show(Request $request, FarmerCalendarEvent $event): StreamedResponse
    {
        abort_unless($event->damage_photo_path, 404);

        $user = $request->user();
        abort_unless($user, 403);

        $canView = $user->isAdmin()
            || $event->user_id === $user->id
            || (
                $user->isLguValidator()
                && $user->normalizedLguMunicipality()
                && strtoupper((string) $event->user?->preferred_municipality) === $user->normalizedLguMunicipality()
            );

        abort_unless($canView, 403);
        abort_unless(Storage::disk('public')->exists($event->damage_photo_path), 404);

        return Storage::disk('public')->response($event->damage_photo_path, $event->damage_photo_original_name);
    }
}
