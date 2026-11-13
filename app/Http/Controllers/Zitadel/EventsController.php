<?php

namespace App\Http\Controllers\Zitadel;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Ereignisse aus Zitadel (#6, Actions v2): `user.removed` löscht Konto und Zugehörigkeiten (Löschen von Personendaten,
 * DB-Standard B3). Die Weitergabe an Module als S4-Ereignis `ident.user.deleted` folgt.
 */
final class EventsController
{
    public function __invoke(Request $request): Response
    {
        $type = (string) $request->input('event_type');
        $userId = (string) $request->input('aggregateID');
        if ($type === 'user.removed' && $userId !== '') {
            $deleted = Account::query()->where('issuer', config('ident.issuer'))->where('subject', $userId)->delete();
            Log::info('ident.user.removed', ['subject_hash' => hash('sha256', $userId), 'deleted' => $deleted]);
        }

        return response()->noContent();
    }
}
