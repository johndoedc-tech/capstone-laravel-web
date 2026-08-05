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

        return $this->serveEvidenceFile(
            $request,
            $event,
            $event->damage_photo_path,
            $event->damage_photo_original_name
        );
    }

    public function showEvidence(Request $request, FarmerCalendarEvent $event): StreamedResponse
    {
        $path = $event->evidence_photo_path ?: $event->damage_photo_path;
        $originalName = $event->evidence_photo_original_name ?: $event->damage_photo_original_name;

        abort_unless($path, 404);

        return $this->serveEvidenceFile($request, $event, $path, $originalName);
    }

    private function serveEvidenceFile(Request $request, FarmerCalendarEvent $event, string $path, ?string $originalName): StreamedResponse
    {
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
        abort_unless(Storage::disk('public')->exists($path), 404);

        $response = Storage::disk('public')->response($path, $originalName);
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
