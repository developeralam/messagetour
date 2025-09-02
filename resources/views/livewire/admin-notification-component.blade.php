<div x-data="{ open: false }" class="relative" x-cloak>
    <x-button @click="open = !open" label="Notification" badge="{{ $notificationsCount ?? 23 }}" icon="o-bell"
        class="btn-ghost btn-sm" responsive />
    <!-- Notifications Dropdown -->
    <!-- Dropdown Panel -->
    <div class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg z-50" x-show="open"
        @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1">
        <div class="p-2 bg-white flex justify-between items-center">
            <span class="font-bold text-gray-700">Notifications</span>
            <button class="text-sm text-blue-600 hover:underline">Mark all as read</button>
        </div>

        <!-- Notification List -->
        <ul class="max-h-60 overflow-y-auto">
            <!-- Example Loop (Blade) -->
            @foreach ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $bgClass = $isUnread ? 'bg-blue-50' : 'bg-white';
                @endphp

                <li class="px-4 py-2 border-b border-gray-100 {{ $bgClass }}">
                    <div class="text-sm text-gray-700 font-medium">
                        <!-- Example of using notification data -->
                        {{ $notification->data['message'] ?? 'Notification message here...' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 flex justify-between items-center">
                        <span>{{ $notification->created_at->diffForHumans() }}</span>

                        <!-- Mark as Read Button -->
                        @if ($isUnread)
                            <button class="px-2 py-1 text-xs text-blue-600 hover:underline"
                                wire:click="markAsRead('{{ $notification->id }}')" {{-- or an Alpine/JS function --}}>
                                Mark as read
                            </button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="p-2 text-center">
            @if (auth()->user()->type == \App\Enum\UserType::Admin)
                <a href="{{ url('/admin/notifications') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            @elseif (auth()->user()->type == \App\Enum\UserType::Agent)
                <a href="{{ url('/partner/notifications') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            @elseif (auth()->user()->type == \App\Enum\UserType::Customer)
                <a href="{{ url('/notifications') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            @endif
        </div>
    </div>
</div>
