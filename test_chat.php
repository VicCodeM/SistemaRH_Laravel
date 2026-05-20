<?php
use App\Models\ChatRoom;
$admin = App\Models\User::where('rol','admin')->first();
echo 'Admin: '.$admin->email.PHP_EOL;

// Misma consulta que ChatList::render
$rooms = ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $admin->id))
    ->where(function ($q) use ($admin) {
        $q->whereHas('miembros', fn ($m) => $m->where('user_id', $admin->id)->whereNull('chat_room_members.hidden_at'))
          ->orWhereHas('mensajes', function ($mq) use ($admin) {
              $mq->where('chat_messages.created_at', '>', function ($sub) use ($admin) {
                  $sub->select('hidden_at')->from('chat_room_members')
                      ->whereColumn('chat_room_members.chat_room_id', 'chat_messages.chat_room_id')
                      ->where('chat_room_members.user_id', $admin->id)->limit(1);
              });
          });
    })->latest('updated_at')->get();

echo 'Rooms visibles: '.$rooms->count().PHP_EOL;
foreach ($rooms as $r) { echo ' - #'.$r->id.' '.$r->nombre.' (msgs='.$r->mensajes()->count().')'.PHP_EOL; }

// Total de rooms del admin (incluye ocultas)
$total = ChatRoom::whereHas('miembros', fn ($q) => $q->where('user_id', $admin->id))->count();
echo 'Rooms totales del admin: '.$total.PHP_EOL;
