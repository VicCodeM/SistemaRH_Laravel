<?php
auth()->login(App\Models\User::where('rol','admin')->first());
$kernel = app(Illuminate\Contracts\Http\Kernel::class);

foreach (['/chat/999999', '/chat'] as $path) {
    $req = Illuminate\Http\Request::create($path, 'GET');
    $resp = $kernel->handle($req);
    echo $path.' -> '.$resp->getStatusCode();
    if ($resp->isRedirect()) echo ' redirect a '.$resp->headers->get('Location');
    echo PHP_EOL;
}
