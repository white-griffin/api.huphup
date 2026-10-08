<script>
    window.chatConfig = {
        socketUrl: @js(config('chat.socket_url')),
    };
</script>

@vite('resources/js/app.js')
