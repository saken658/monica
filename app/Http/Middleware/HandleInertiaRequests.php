<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @return array
     */
    public function share(Request $request)
    {
        return [
            ...parent::share($request),
            'help_links' => fn () => config('monica.help_links'),
            'help_url' => fn () => config('monica.help_center_url'),
            'footer' => fn () => $this->footer(),
            'auth' => fn () => [
                'user' => auth()->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sentry' => fn () => [
                'dsn' => config('sentry.dsn'),
                'tunnel' => config('sentry-tunnel.tunnel-url'),
                'release' => config('sentry.release'),
                'environment' => config('sentry.environment'),
                'sendDefaultPii' => config('sentry.send_default_pii'),
                'tracesSampleRate' => config('sentry.traces_sample_rate'),
            ],
        ];
    }

    private function footer(): string
    {
        $commit = config('monica.commit');
        $params = [
            'version' => config('monica.app_version'),
            'short' => substr(config('monica.commit'), 0, 7),
        ];
        if ($commit === null) {
            $message = trans('Version :version.', $params);
        } else {
            $params['url'] = Str::finish(config('monica.repository', 'https://github.com/monicahq/monica/'), '/').'commit/'.config('monica.commit');
            $message = trans('Version :version — commit [:short](:url).', $params);
        }

        return Str::markdownExternalLink($message, 'underline text-xs dark:text-gray-100 hover:text-gray-900 hover:dark:text-gray-200');
    }
}
