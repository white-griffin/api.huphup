<x-filament-panels::page>

    <div
        x-data="supportChat({
            conversationId: @js((string) $this->record->id),
            currentAdminChatUserId: @js($currentAdminChatUserId),
            initialMessages: @js($chatMessages),
            senderMeta: @js($senderMeta),
        })"
        x-init="init()"
        x-on:beforeunload.window="destroy()"
        class="flex flex-col gap-4"
    >

        {{-- Messages --}}
        <div
            x-ref="messagesContainer"
            class="flex h-[calc(100vh-22rem)] flex-col overflow-y-auto rounded-xl bg-gray-50 p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-950 dark:ring-white/10"
        >
            <div class="flex flex-col gap-3">

                <template
                    x-for="message in messages"
                    :key="message.id"
                >
                    <div
                        class="flex w-full"
                        :class="
                            String(message.senderId) === String(currentAdminChatUserId)
                                ? 'justify-start'
                                : 'justify-end'
                        "
                    >
                        <div
                            class="max-w-[75%] rounded-2xl px-4 py-3"
                            :class="
                                String(message.senderId) === String(currentAdminChatUserId)
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-white text-gray-950 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-white dark:ring-white/10'
                            "
                        >

                            <div class="flex items-start gap-3">

                                <div class="shrink-0">
                                    <template x-if="message.senderAvatar">
                                        <img
                                            :src="message.senderAvatar"
                                            :alt="message.senderName"
                                            class="h-9 w-9 rounded-full object-cover"
                                        >
                                    </template>

                                    <template x-if="!message.senderAvatar">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                                            x-text="message.senderInitials"
                                        ></div>
                                    </template>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="mb-1 text-xs opacity-70"
                                        x-text="
                String(message.senderId) === String(currentAdminChatUserId)
                    ? 'You'
                    : message.senderName
            "
                                    ></div>

                                    <template x-if="message.type === 'TEXT'">
                                        <div
                                            class="whitespace-pre-wrap break-words text-sm"
                                            x-text="message.content"
                                        ></div>
                                    </template>

                                    <template x-if="message.type === 'IMAGE'">
                                        <div class="text-sm">
                                            <span x-text="message.content"></span>
                                        </div>
                                    </template>

                                    <template x-if="message.type === 'FILE'">
                                        <div class="text-sm">
                                            <span x-text="message.content"></span>
                                        </div>
                                    </template>

                                    <div
                                        class="mt-2 text-[10px] opacity-60"
                                        x-text="
                message.createdAt
                    ? new Date(message.createdAt).toLocaleString()
                    : ''
            "
                                    ></div>
                                </div>

                            </div>

                        </div>
                    </div>
                </template>

                {{-- Empty --}}
                <template x-if="messages.length === 0">
                    <div class="flex h-full min-h-32 items-center justify-center">
                        <span class="text-sm text-gray-500">
                            پیامی وجود ندارد .
                        </span>
                    </div>
                </template>

            </div>
        </div>

        {{-- Typing indicator --}}
        <div
            x-show="typingUsers.size > 0"
            x-cloak
            class="text-xs text-gray-500"
        >
            درحال نوشتن . . .
        </div>

        {{-- Message form --}}
        <form
            x-on:submit.prevent="sendMessage()"
            class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="flex items-end gap-3">

                <div class="flex-1">

                    <x-filament::input.wrapper>

                        <textarea
                            x-model="messageContent"
                            x-on:input="startTyping()"
                            x-on:blur="stopTyping()"
                            x-on:keydown.enter.exact.prevent="sendMessage()"
                            rows="2"
                            placeholder="پیام خود را بنویسید . . ."
                            class="block w-full resize-none border-0 bg-transparent px-3 py-2 text-sm focus:ring-0"
                        ></textarea>

                    </x-filament::input.wrapper>

                </div>

                <x-filament::button
                    type="submit"
                    icon="heroicon-m-paper-airplane"
                >
                    ارسال
                </x-filament::button>

            </div>
        </form>

    </div>

</x-filament-panels::page>
