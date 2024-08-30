@extends('layouts.master')

@section('content')
    <div id="chat-widget" class="chat-widget">
        <div id="chat-header" class="chat-header">
            <span>Chat</span>
            <button id="toggle-chat" class="toggle-chat">&minus;</button>
        </div>
        <div id="chat-content" class="chat-content">
            <div id="messages" class="messages-container">
                <!-- Messages will be appended here -->
            </div>
            <div id="typing-indicator"></div>
            <div class="input-container">
                <input type="hidden" id="to-user-id" value="{{ $toUser->id }}">
                <input type="text" id="message-input" placeholder="Type your message...">
                <button id="send-button">Send</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@push('styles')
    <link href="{{ mix('css/chat.css') }}" rel="stylesheet">
@endpush
