<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('app.deploy_secret');

        // Verificar firma de GitHub
        $firma = $request->header('X-Hub-Signature-256');
        if (!$firma || !$secret) {
            return response('Unauthorized', 401);
        }

        $esperada = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($esperada, $firma)) {
            return response('Unauthorized', 401);
        }

        // Solo reaccionar a push en main
        $payload = json_decode($request->getContent(), true);
        if (($payload['ref'] ?? '') !== 'refs/heads/main') {
            return response('Ignorado (no es main)', 200);
        }

        $commit = $payload['head_commit']['message'] ?? 'sin mensaje';
        Log::info("Webhook deploy recibido: {$commit}");

        // Correr deploy en background para no hacer timeout
        $script = base_path('deploy.sh');
        exec("bash {$script} > /dev/null 2>&1 &");

        return response()->json(['ok' => true, 'commit' => $commit]);
    }
}
