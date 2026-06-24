@extends('layouts.app')

@section('title', 'Chat — Bug #' . $bug->id)

@section('content')
<div class="space-y-6">
    <!-- Back Link & Header -->
    <div class="border-b border-slate-200 pb-4">
        <a href="{{ route('bugs.show', $bug) }}" class="text-xs text-slate-500 hover:text-blue-600 font-mono tracking-wider uppercase inline-flex items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> KEMBALI KE DETAIL BUG
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">CHAT — BUG #{{ $bug->id }}</h1>
            @if($bug->status === 'OPEN')
                <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> OPEN
                </span>
            @else
                <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded uppercase">CLOSED</span>
            @endif
        </div>
        <p class="text-xs text-slate-500 font-mono mt-1">{{ $bug->title }}</p>
        <div class="mt-2 text-xs text-slate-500 font-mono">
            @if(Auth::user()->role === 'reporter')
                MEKANIK: <strong class="text-slate-700">{{ $bug->assignee->name }}</strong>
            @else
                REPORTER: <strong class="text-slate-700">{{ $bug->reporter->name }}</strong>
            @endif
        </div>
    </div>

    <!-- Chat Container -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col" style="height: calc(100vh - 280px); min-height: 400px;">
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-box">
            @foreach($chats as $chat)
            <div class="chat-bubble {{ $chat->sender_id === Auth::id() ? 'bubble-self' : 'bubble-other' }}"
                 data-id="{{ $chat->id }}">
                @if($chat->sender_id === Auth::id())
                    {{-- Outbound message: right aligned --}}
                    <div class="flex justify-end">
                        <div class="max-w-[70%]">
                            <div class="bg-blue-600 text-white rounded-xl rounded-br-sm px-4 py-2.5 shadow-sm">
                                <p class="text-sm leading-relaxed">{{ $chat->message }}</p>
                            </div>
                            <div class="flex justify-end items-center gap-2 mt-1">
                                <span class="text-[10px] font-mono text-slate-400">{{ $chat->created_at->format('d M Y, H:i') }}</span>
                                <span class="text-[10px] font-bold text-slate-500">{{ $chat->sender->name }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Inbound message: left aligned --}}
                    <div class="flex justify-start">
                        <div class="max-w-[70%]">
                            <div class="bg-slate-100 text-slate-800 rounded-xl rounded-bl-sm px-4 py-2.5 border border-slate-200">
                                <p class="text-sm leading-relaxed">{{ $chat->message }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-slate-500">{{ $chat->sender->name }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $chat->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Input Bar -->
        <div class="border-t border-slate-200 p-4 bg-slate-50/50 rounded-b-xl">
            <div class="flex gap-3 items-center">
                <input type="text" id="msg-input"
                    class="flex-1 bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400"
                    placeholder="Tulis pesan..." autocomplete="off" />
                <button id="btn-send"
                    class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition-all shadow-sm active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="bi bi-send-fill"></i> Kirim
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const bugId   = {{ $bug->id }};
    const myId    = {{ Auth::id() }};
    const chatBox = document.getElementById('chat-box');
    const input   = document.getElementById('msg-input');
    const btnSend = document.getElementById('btn-send');

    // Scroll ke bawah
    function scrollBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    scrollBottom();

    // Ambil last ID pesan terakhir di DOM
    function getLastId() {
        const bubbles = chatBox.querySelectorAll('[data-id]');
        if (bubbles.length === 0) return 0;
        return parseInt(bubbles[bubbles.length - 1].dataset.id);
    }

    // Render satu pesan baru ke DOM
    function renderMessage(msg) {
        const isSelf = msg.sender_id === myId;
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (isSelf ? 'bubble-self' : 'bubble-other');
        div.dataset.id = msg.id;

        if (isSelf) {
            div.innerHTML = `
                <div class="flex justify-end">
                    <div class="max-w-[70%]">
                        <div class="bg-blue-600 text-white rounded-xl rounded-br-sm px-4 py-2.5 shadow-sm">
                            <p class="text-sm leading-relaxed">${escapeHtml(msg.message)}</p>
                        </div>
                        <div class="flex justify-end items-center gap-2 mt-1">
                            <span class="text-[10px] font-mono text-slate-400">${msg.created_at}</span>
                            <span class="text-[10px] font-bold text-slate-500">${escapeHtml(msg.sender)}</span>
                        </div>
                    </div>
                </div>`;
        } else {
            div.innerHTML = `
                <div class="flex justify-start">
                    <div class="max-w-[70%]">
                        <div class="bg-slate-100 text-slate-800 rounded-xl rounded-bl-sm px-4 py-2.5 border border-slate-200">
                            <p class="text-sm leading-relaxed">${escapeHtml(msg.message)}</p>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-bold text-slate-500">${escapeHtml(msg.sender)}</span>
                            <span class="text-[10px] font-mono text-slate-400">${msg.created_at}</span>
                        </div>
                    </div>
                </div>`;
        }

        chatBox.appendChild(div);
        scrollBottom();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Kirim pesan
    btnSend.addEventListener('click', async () => {
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        btnSend.disabled = true;

        try {
            const res = await fetch(`/bugs/${bugId}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message: msg }),
            });

            const data = await res.json();
            renderMessage(data);
        } catch (e) {
            console.error('Failed to send message:', e);
        }

        btnSend.disabled = false;
        input.focus();
    });

    // Enter untuk kirim
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') btnSend.click();
    });

    // Polling setiap 3 detik
    setInterval(async () => {
        try {
            const lastId = getLastId();
            const res = await fetch(`/bugs/${bugId}/chat/poll?last_id=${lastId}`);
            const messages = await res.json();
            messages.forEach(renderMessage);
        } catch (e) {
            // silently fail on poll errors
        }
    }, 3000);
</script>
@endsection
