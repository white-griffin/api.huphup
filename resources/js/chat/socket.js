import { io } from 'socket.io-client';

let socket = null;

export async function connectChatSocket() {
    if (socket?.connected) {
        return socket;
    }

    const response = await fetch('/admin/chat/token', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error('Failed to get chat token.');
    }

    const data = await response.json();

    socket = io(import.meta.env.VITE_CHAT_SOCKET_URL, {
        auth: {
            token: data.token,
        },
    });

    await new Promise((resolve, reject) => {
        socket.once('connect', resolve);

        socket.once('connect_error', reject);
    });

    return socket;
}

export function getChatSocket() {
    return socket;
}

export function disconnectChatSocket() {
    if (socket) {
        socket.disconnect();
        socket = null;
    }
}
