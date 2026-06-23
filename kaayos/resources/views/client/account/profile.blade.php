@extends('layouts.client')

@section('title', 'Account')
@section('page_title', 'Account')

@section('content')

@php
    $user = auth()->user();

    $initialData = json_encode([
        'fullName'           => $user->name ?? '',
        'email'              => $user->email ?? '',
        'phone'              => $user->phone ?? '',
        'barangay'           => $user->city ?? '',
        'avatarUrl'          => $user->avatar ? \Illuminate\Support\Facades\Storage::url($user->avatar) : null,
        'emailNotifications' => $user->email_notifications ?? 'All updates',
        'language'           => $user->language ?? 'English',
    ], JSON_HEX_APOS | JSON_HEX_TAG);
@endphp

<div
    id="account-root"
    data-initial="{{ $initialData }}"
></div>

@endsection

@push('scripts')
    <script>
        window.authToken = "{{ auth()->user()->createToken('account-page')->plainTextToken }}";
    </script>
    @vite(['resources/js/client/account.jsx'])
@endpush