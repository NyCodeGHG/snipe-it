<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiUserAgent
{
    /**
     * Substrings (case-insensitive) that identify a generic, scripted, or default
     * non-browser HTTP client. When the require_user_agent_on_api setting is on,
     * requests with an empty User-Agent or one matching any of these are denied.
     *
     * The expectation is that requests originating from a logged-in browser
     * (snipeit.js, datatables, select2, etc.) carry the browser's own User-Agent
     * and so are unaffected.
     */
    private const GENERIC_USER_AGENT_PATTERNS = [
        'curl/',
        'wget/',
        'python-requests/',
        'python-urllib',
        'PostmanRuntime/',
        'insomnia/',
        'Go-http-client/',
        'okhttp/',
        'HTTPie/',
        'Apache-HttpClient/',
        'Java/',
        'Faraday',
        'http.rb/',
        'libwww-perl/',
        'Ruby',
        'node-fetch/',
        'axios/',
        'GuzzleHttp/',
        'RestSharp/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::getSettings()?->require_user_agent_on_api) {
            $userAgent = trim((string) $request->header('User-Agent', ''));

            if ($userAgent === '' || $this->isGenericUserAgent($userAgent)) {
                return new JsonResponse([
                    'status' => 'error',
                    'messages' => trans('admin/settings/general.api_user_agent_required'),
                    'payload' => null,
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }

    private function isGenericUserAgent(string $userAgent): bool
    {
        foreach (self::GENERIC_USER_AGENT_PATTERNS as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
