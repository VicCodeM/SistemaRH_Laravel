<?php
try {
    Illuminate\Support\Facades\Cache::put('chat_test_x', true, now()->addSeconds(10));
    echo 'Cache OK: '.(Illuminate\Support\Facades\Cache::get('chat_test_x') ? 'si' : 'no').PHP_EOL;
    Illuminate\Support\Facades\Cache::forget('chat_test_x');
} catch (\Throwable $e) {
    echo 'Cache ERROR: '.$e->getMessage().PHP_EOL;
}
