<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.3/dist/sweetalert2.min.css">

</head>

<body class="font-sans antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif



        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.3/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            @auth
            const userId = {{ auth()->id() }};
            const newMessageNotificationChannel = Echo.private(`notifications.${userId}`);
            const baseUrl = "{{ url('groups') }}/";
            const userGroupUrl = window.location.href.match(/\/groups\/(\d+)/);
            newMessageNotificationChannel.subscribed(() => {
                console.log('subscribed to newMessageNotificationChannel');
            }).listen('.new-group-notification', (e) => {
                const groupName = e.groupName; // Extract the group name from the event payload
                const groupId = e.groupId

                showNewGroupNotification(groupName, groupId);
            });
            @foreach ($groups = auth()->user()->groups as $group)
                const groupChannel{{ $group->id }} = Echo.join(`group-channel.{{ $group->id }}`)
                    .here(users => {
                        console.log(`Users currently in Group {{ $group->id }}:`, users);
                    })
                    .joining(user => {
                        console.log(`User ${user.name} joining Group {{ $group->id }}`);
                    })
                    .leaving(user => {
                        console.log(`User ${user.name} leaving Group {{ $group->id }}`);
                    });

                groupChannel{{ $group->id }}.listen(".new-group-message", (e) => {
                    if (userGroupUrl && userGroupUrl[1] && parseInt(userGroupUrl[1]) !== e.message
                        .group_id) {
                        Swal.fire({
                            title: `New Message in ${e.message.group.name}`,
                            text: `${e.message.user.name}: ${e.message.message}`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Open Group',
                            cancelButtonText: 'Dismiss'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirect the user to the group chat or group details page
                                const finalUrl = `${baseUrl}${e.message.group_id}`;
                                window.location.href = finalUrl;
                            }
                        });
                    }
                });
            @endforeach


            function showNewGroupNotification(groupName, groupId) {
                Swal.fire({
                    title: `You were added to a new group`,
                    text: `Group: ${groupName}`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Open Group',
                    cancelButtonText: 'Dismiss'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect the user to the group chat or group details page
                        const finalUrl = `${baseUrl}${groupId}`;
                        window.location.href = finalUrl;
                    }
                });
            }
        @endauth
        });
    </script>
</body>

</html>
