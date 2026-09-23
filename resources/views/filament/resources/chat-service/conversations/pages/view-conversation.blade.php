<x-filament-panels::page>

    {{ $this->infolist }}

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Messages
        </x-slot>

        <div class="max-h-[700px] overflow-y-auto rounded-xl bg-gray-50 p-4 dark:bg-gray-950">
            <div class="space-y-4">

                @forelse ($messages as $message)

                    @php
                        $isOutgoing = (string) $message->senderId === (string) $this->record->createdBy;
                        $isDeleted = $message->deletedAt !== null;
                        $isEdited = $message->editedAt !== null;

                        $replyToId = $message->getAttribute('replyTo');

                        $reply = $replyToId
                            ? $replies->get((string) $replyToId)
                            : null;

                        $nickname = $message->sender?->nickname ?? 'Unknown';
                        $initial = strtoupper(mb_substr($nickname, 0, 1));
                    @endphp

                    <div class="flex w-full {{ $isOutgoing ? 'justify-end' : 'justify-start' }}">

                        <div class="flex max-w-[75%] items-end gap-2">

                            @if (! $isOutgoing)
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $initial }}
                                </div>
                            @endif

                            <div class="min-w-0">

                                <div class="mb-1 {{ $isOutgoing ? 'mr-1 text-right' : 'ml-1' }} text-xs text-gray-500">
                                    {{ $nickname }}
                                </div>

                                <div
                                    class="
                                        rounded-2xl px-4 py-3 shadow-sm
                                        {{ $isOutgoing
                                            ? 'rounded-br-md bg-primary-600'
                                            : 'rounded-bl-md bg-white ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700'
                                        }}
                                    "
                                >

                                    @if ($reply)
                                        <div
                                            class="
                                                mb-3 border-l-4 pl-3 text-xs
                                                {{ $isOutgoing
                                                    ? 'border-white/50 text-white/70'
                                                    : 'border-gray-400 text-gray-500 dark:border-gray-500'
                                                }}
                                            "
                                        >
                                            <div class="font-medium">
                                                {{ $reply->sender?->nickname ?? 'Unknown' }}
                                            </div>

                                            <div class="mt-1 truncate">
                                                @if ($reply->deletedAt)
                                                    Message deleted
                                                @elseif ($reply->type === 'IMAGE')
                                                    📷 Image
                                                @elseif ($reply->type === 'FILE')
                                                    📎 File
                                                @else
                                                    {{ $reply->content ?? 'Message' }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($isDeleted)

                                        <div class="{{ $isOutgoing ? 'text-white/60' : 'text-gray-400' }} italic">
                                            Message deleted
                                        </div>

                                    @elseif ($message->type === 'TEXT')

                                        <div
                                            class="
                                                whitespace-pre-wrap break-words text-sm
                                                {{ $isOutgoing
                                                    ? 'text-white'
                                                    : 'text-gray-900 dark:text-gray-100'
                                                }}
                                            "
                                        >
                                            {{ $message->content }}
                                        </div>

                                    @elseif ($message->type === 'IMAGE')

                                        <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
                                            <span class="text-sm">
                                                📷 Image
                                            </span>
                                        </div>

                                    @elseif ($message->type === 'FILE')

                                        <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
                                            <span class="text-sm">
                                                📎 {{ $message->content ?? 'File' }}
                                            </span>
                                        </div>

                                    @endif

                                    <div
                                        class="
                                            mt-2 flex items-center justify-end gap-2 text-[11px]
                                            {{ $isOutgoing ? 'text-white/60' : 'text-gray-400' }}
                                        "
                                    >
                                        @if ($isEdited)
                                            <span>edited</span>
                                        @endif

                                        <span>
                                            {{ $message->createdAt?->format('H:i') }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                            @if ($isOutgoing)
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                    {{ $initial }}
                                </div>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="py-12 text-center text-sm text-gray-500">
                        No messages found.
                    </div>

                @endforelse

            </div>
        </div>

    </x-filament::section>

</x-filament-panels::page>
