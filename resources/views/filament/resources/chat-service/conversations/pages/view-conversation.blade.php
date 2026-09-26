<x-filament-panels::page>

    {{ $this->infolist }}

    <div
        x-data="{ tab: 'messages' }"
        class="mt-6"
    >

        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-6">

                <button
                    type="button"
                    x-on:click="tab = 'messages'"
                    class="border-b-2 px-1 pb-3 text-sm font-medium transition"
                    :class="tab === 'messages'
                        ? 'border-primary-600 text-primary-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    پیام ها
                </button>

                <button
                    type="button"
                    x-on:click="tab = 'members'"
                    class="border-b-2 px-1 pb-3 text-sm font-medium transition"
                    :class="tab === 'members'
                        ? 'border-primary-600 text-primary-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    اعضا
                </button>

            </nav>
        </div>

        {{-- Messages --}}
        <div
            x-show="tab === 'messages'"
            x-cloak
            class="pt-6"
        >

            <x-filament::section>
                <x-slot name="heading">
                    پیام ها
                </x-slot>

                <div class="max-h-[700px] overflow-y-auto rounded-xl bg-gray-50 p-4 dark:bg-gray-950">
                    <div class="space-y-4">

                        @forelse ($messages as $message)

                            @php
                                $chatUser = $message->sender;

                                $mysqlUser = null;

                                if (
                                    $chatUser &&
                                    $chatUser->externalType === 'USER'
                                ) {
                                    $mysqlUser = $users[(int) $chatUser->externalId] ?? null;
                                }

                                $nickname = $mysqlUser
                                    ? trim($mysqlUser->first_name . ' ' . $mysqlUser->last_name)
                                    : ($chatUser?->nickname ?? 'Unknown');

                                $initial = strtoupper(
                                    mb_substr($nickname, 0, 1)
                                );

                                $isOutgoing =
                                    (string) $message->senderId ===
                                    (string) $this->record->createdBy;

                                $isDeleted = $message->deletedAt !== null;
                                $isEdited = $message->editedAt !== null;

                                $replyToId = $message->getAttribute('replyTo');

                                $reply = $replyToId
                                    ? $replies->get((string) $replyToId)
                                    : null;
                            @endphp

                            <div class="flex w-full {{ $isOutgoing ? 'justify-end' : 'justify-start' }}">

                                <div class="flex max-w-[75%] items-end gap-2">

                                    @if (! $isOutgoing)
                                        <div class="h-8 w-8 shrink-0">
                                            @if ($mysqlUser?->avatar_url)
                                                <img
                                                    src="{{ $mysqlUser->avatar_url }}"
                                                    alt="{{ $nickname }}"
                                                    class="h-8 w-8 rounded-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                    {{ $initial }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="min-w-0">

                                        <div class="mb-1 text-xs text-gray-500 {{ $isOutgoing ? 'mr-1 text-right' : 'ml-1' }}">
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

                                                @php
                                                    $replyChatUser = $reply->sender;

                                                    $replyUser = null;

                                                    if (
                                                        $replyChatUser &&
                                                        $replyChatUser->externalType === 'USER'
                                                    ) {
                                                        $replyUser = $users[(int) $replyChatUser->externalId] ?? null;
                                                    }

                                                    $replyName = $replyUser
                                                        ? trim($replyUser->first_name . ' ' . $replyUser->last_name)
                                                        : ($replyChatUser?->nickname ?? 'Unknown');
                                                @endphp

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
                                                        {{ $replyName }}
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
                                        <div class="h-8 w-8 shrink-0">
                                            @if ($mysqlUser?->avatar_url)
                                                <img
                                                    src="{{ $mysqlUser->avatar_url }}"
                                                    alt="{{ $nickname }}"
                                                    class="h-8 w-8 rounded-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                    {{ $initial }}
                                                </div>
                                            @endif
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

        </div>

        {{-- Members --}}
        <div
            x-show="tab === 'members'"
            x-cloak
            class="pt-6"
        >

            <x-filament::section>
                <x-slot name="heading">
                    اعضا
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">

                        <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 flex items-center font-medium text-gray-500">
                                کاربر
                            </th>

                            <th class="px-4 py-3 font-medium text-gray-500">
                                نقش
                            </th>



                            <th class="px-4 py-3 font-medium text-gray-500">
                                تاریخ عضویت
                            </th>

                            <th class="px-4 py-3 font-medium text-gray-500">
                                وضعیت
                            </th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

                        @forelse ($members as $member)

                            @php
                                $chatUser = $member->user;

                                $mysqlUser = null;

                                if (
                                    $chatUser &&
                                    $chatUser->externalType === 'USER'
                                ) {
                                    $mysqlUser = $users[(int) $chatUser->externalId] ?? null;
                                }

                                $name = $mysqlUser
                                    ? trim($mysqlUser->first_name . ' ' . $mysqlUser->last_name)
                                    : ($chatUser?->nickname ?? 'Unknown');

                                $initial = strtoupper(
                                    mb_substr($name, 0, 1)
                                );
                            @endphp

                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">

                                        @if ($mysqlUser?->avatar_url)
                                            <img
                                                src="{{ $mysqlUser->avatar_url }}"
                                                alt="{{ $name }}"
                                                class="h-9 w-9 rounded-full object-cover"
                                            >
                                        @else
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $initial }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $name }}
                                            </div>

                                            @if ($mysqlUser?->mobile)
                                                <div class="text-xs text-gray-500">
                                                    {{ $mysqlUser->mobile }}
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    {{ $chatUser?->externalType ?? '-' }}
                                </td>



                                <td class="px-4 py-3 text-gray-500">
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($member->joinedAt)->format('Y-m-d H:i') ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    @if ($member->leftAt)
                                        <x-filament::badge color="danger">
                                            خارج شده
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="success">
                                            فعال
                                        </x-filament::badge>
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="5"
                                    class="px-4 py-12 text-center text-sm text-gray-500"
                                >
                                    کاربر پیدا نشد.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>
                </div>

            </x-filament::section>

        </div>

    </div>

</x-filament-panels::page>
