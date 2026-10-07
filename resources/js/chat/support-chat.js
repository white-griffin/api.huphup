import {
    connectChatSocket,
    getChatSocket,
    disconnectChatSocket,
} from './socket';

export function supportChat({
    conversationId,
    currentAdminChatUserId,
    initialMessages = [],
}) {
    return {
        conversationId,
        currentAdminChatUserId,

        socket: null,

        messages: initialMessages,

        typingUsers: new Set(),

        initialized: false,

        messageContent: '',

        async init() {
            if (this.initialized) {
                return;
            }

            this.initialized = true;

            try {
                this.socket = await connectChatSocket();
                console.log('Chat socket connected:', this.socket.id);

                this.registerEvents();

                this.joinConversation();

                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            } catch (error) {
                console.error(
                    'Failed to initialize support chat:',
                    error
                );
            }
        },

        registerEvents() {
            this.socket.on(
                'message:history',
                (result) => {
                    if (
                        result.conversationId &&
                        String(result.conversationId) !==
                        String(this.conversationId)
                    ) {
                        return;
                    }

                    this.messages = (result.messages ?? [])
                        .map(message => this.normalizeMessage(message));

                    this.scrollToBottom();
                }
            );

            this.socket.on(
                'message:new',
                (message) => {
                    if (
                        String(message.conversationId) !==
                        String(this.conversationId)
                    ) {
                        return;
                    }

                    const normalized =
                        this.normalizeMessage(message);

                    if (
                        this.messages.some(
                            item =>
                                String(item.id) ===
                                String(normalized.id)
                        )
                    ) {
                        return;
                    }

                    this.messages.push(normalized);

                    this.scrollToBottom();
                }
            );

            this.socket.on(
                'user:typing',
                (data) => {
                    if (
                        String(data.userId) ===
                        String(this.currentAdminChatUserId)
                    ) {
                        return;
                    }

                    this.typingUsers.add(
                        String(data.userId)
                    );
                }
            );

            this.socket.on(
                'user:stop-typing',
                (data) => {
                    this.typingUsers.delete(
                        String(data.userId)
                    );
                }
            );

            this.socket.on(
                'message:read',
                (data) => {
                    console.log(
                        'Message read:',
                        data
                    );
                }
            );

            this.socket.on(
                'conversation:read',
                (data) => {
                    console.log(
                        'Conversation read:',
                        data
                    );
                }
            );

            this.socket.on(
                'message:error',
                (data) => {
                    console.error(
                        'Message error:',
                        data
                    );
                }
            );

            this.socket.on(
                'conversation:error',
                (data) => {
                    console.error(
                        'Conversation error:',
                        data
                    );
                }
            );
        },

        normalizeMessage(message) {
            return {
                id: String(message.id ?? message._id),
                conversationId: String(message.conversationId),
                senderId: String(message.senderId),
                senderName: message.senderName
                    ?? message.sender?.nickname
                    ?? 'Unknown',
                senderAvatar: message.senderAvatar ?? null,
                senderInitials: message.senderInitials ?? 'U',
                type: message.type ?? 'TEXT',
                content: message.content ?? '',
                createdAt: message.createdAt ?? null,
            };
        },

        joinConversation() {
            this.socket.emit(
                'conversation:join',
                this.conversationId
            );
        },

        sendMessage() {
            const content = this.messageContent.trim();

            if (!content) {
                return;
            }

            if (!this.socket?.connected) {
                console.error('Chat socket is not connected.');
                return;
            }

            console.log('Sending message:', {
                conversationId: this.conversationId,
                content,
            });

            this.socket.emit('message:send', {
                conversationId: this.conversationId,
                type: 'TEXT',
                content,
                replyTo: null,
            });

            this.messageContent = '';
            this.stopTyping();
        },

        startTyping() {
            if (!this.socket?.connected) {
                return;
            }

            this.socket.emit('user:typing', this.conversationId);
        },

        stopTyping() {
            if (!this.socket?.connected) {
                return;
            }

            this.socket.emit('user:stop-typing', this.conversationId);
        },

        markAsRead(messageId) {
            this.socket.emit(
                'message:read',
                messageId
            );
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container =
                    this.$refs.messagesContainer;

                if (!container) {
                    return;
                }

                container.scrollTop =
                    container.scrollHeight;
            });
        },

        destroy() {
            this.socket?.off('message:history');
            this.socket?.off('message:new');
            this.socket?.off('user:typing');
            this.socket?.off('user:stop-typing');
            this.socket?.off('message:read');
            this.socket?.off('conversation:read');
            this.socket?.off('message:error');
            this.socket?.off('conversation:error');
        },
    };
}
