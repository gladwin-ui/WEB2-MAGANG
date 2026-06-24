<?php
namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\BugChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BugChatController extends Controller
{
    // Tampilkan halaman chat
    public function show(Bug $bug) {
        $user = Auth::user();

        // Reporter hanya bisa chat jika bug miliknya dan sudah ada yang assign
        if ($user->role === 'reporter') {
            if ($bug->reported_by !== $user->id) abort(403);
            if (is_null($bug->assigned_to)) abort(403, 'Belum ada mekanik yang mengambil laporan ini.');
        }

        // Mekanik hanya bisa chat jika dia yang assign
        if ($user->role === 'mekanik') {
            if ($bug->assigned_to !== $user->id) abort(403, 'Kamu belum mengambil laporan ini.');
        }

        $bug->load(['reporter', 'assignee']);
        $chats = $bug->chats()->with('sender')->get();
        return view('chats.show', compact('bug', 'chats'));
    }

    // Kirim pesan (dipanggil via fetch/AJAX)
    public function send(Request $request, Bug $bug) {
        $user = Auth::user();

        // Validasi akses sama seperti show()
        if ($user->role === 'reporter') {
            if ($bug->reported_by !== $user->id) abort(403);
            if (is_null($bug->assigned_to)) abort(403);
        }
        if ($user->role === 'mekanik') {
            if ($bug->assigned_to !== $user->id) abort(403);
        }

        $request->validate(['message' => 'required|min:1|max:1000']);

        $chat = BugChat::create([
            'bug_id'    => $bug->id,
            'sender_id' => $user->id,
            'message'   => $request->message,
        ]);

        return response()->json([
            'id'         => $chat->id,
            'message'    => $chat->message,
            'sender'     => $user->name,
            'sender_id'  => $user->id,
            'created_at' => $chat->created_at->format('d M Y, H:i'),
        ]);
    }

    // Polling — ambil pesan baru setelah ID tertentu
    public function poll(Request $request, Bug $bug) {
        $user = Auth::user();

        if ($user->role === 'reporter') {
            if ($bug->reported_by !== $user->id) abort(403);
            if (is_null($bug->assigned_to)) abort(403);
        }
        if ($user->role === 'mekanik') {
            if ($bug->assigned_to !== $user->id) abort(403);
        }

        $lastId = $request->query('last_id', 0);

        $messages = $bug->chats()
            ->with('sender')
            ->where('id', '>', $lastId)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'message'    => $c->message,
                'sender'     => $c->sender->name,
                'sender_id'  => $c->sender_id,
                'created_at' => $c->created_at->format('d M Y, H:i'),
            ]);

        return response()->json($messages);
    }
}
