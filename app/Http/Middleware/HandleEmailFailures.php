<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleEmailFailures
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the response contains email error information
        if ($response->getStatusCode() === 201 || $response->getStatusCode() === 200) {
            $content = json_decode($response->getContent(), true);
            
            if (isset($content['email_sent']) && !$content['email_sent'] && isset($content['email_error'])) {
                // Log the email failure for monitoring
                Log::warning('Email sending failed in request', [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'email_error' => $content['email_error'],
                    'task_id' => $content['id'] ?? null
                ]);

                // Add a warning header to the response
                $response->headers->set('X-Email-Warning', 'Email notification failed to send');
            }
        }

        return $response;
    }
} 