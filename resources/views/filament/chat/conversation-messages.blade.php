<div class="space-y-4">
    @foreach ($messages as $message)
        <div class="flex gap-3">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-950 dark:text-white">
                        {{ $message->sender?->nickname ?? 'Unknown' }}
                    </span>

                    <span class="text-xs text-gray-500">
                        {{ $message->createdAt?->format('Y-m-d H:i') }}
                    </span>

                    @if ($message->editedAt)
                        <span class="text-xs text-gray-400">
                            edited
                        </span>
                    @endif
                </div>

                <div class="mt-1 rounded-lg bg-gray-100 px-4 py-3 dark:bg-gray-800">
                    @if ($message->deletedAt)
                        <span class="italic text-gray-500">
                            Message deleted
                        </span>
                    @elseif ($message->type === 'TEXT')
                        <div class="whitespace-pre-wrap">
                            {{ $message->content }}
                        </div>
                    @elseif ($message->type === 'IMAGE')
                        <div class="text-sm text-gray-500">
                            🖼 Image
                        </div>

                        @if ($message->content)
                            <div class="mt-1">
                                {{ $message->content }}
                            </div>
                        @endif
                    @elseif ($message->type === 'FILE')
                        <div class="text-sm text-gray-500">
                            📎 File
                        </div>

                        @if ($message->content)
                            <div class="mt-1">
                                {{ $message->content }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
