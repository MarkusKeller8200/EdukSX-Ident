<?php

namespace App\Http\Middleware;

use App\Zitadel\SignatureVerifier;
use Closure;
use EduKSX\Core\Http\ProblemException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Route-Middleware `zitadel.signature:<ziel>` – Schlüssel aus config('ident.signing_keys.<ziel>'). */
final class VerifyZitadelSignature
{
    public function handle(Request $request, Closure $next, string $target): Response
    {
        $verifier = new SignatureVerifier((int) config('ident.signature_max_age', 300));
        if (! $verifier->verify($request, config("ident.signing_keys.$target"))) {
            throw new ProblemException(401, 'invalid_signature', 'Signatur fehlt, ist falsch oder zu alt.');
        }

        return $next($request);
    }
}
