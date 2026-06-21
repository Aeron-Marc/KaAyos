@extends('layouts.client')

@section('title', 'Account')
@section('page_title', 'Account')

@section('content')

@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->name ?? 'U', 0, 2));
@endphp

<div class="profile-grid">
    <div class="profile-sidebar-card">
        <div class="profile-big-avatar">{{ $initials }}</div>
        <h3>{{ $user->name ?? 'User' }}</h3>
        <p>{{ $user->email ?? '' }}</p>
        <span class="profile-role-tag">Homeowner</span>
    </div>

    <div>
        <div class="form-section">
            <h3>Personal Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" value="{{ $user->name ?? '' }}" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" value="{{ $user->email ?? '' }}" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" placeholder="09XX XXX XXXX">
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" placeholder="e.g. Acle, Tuy">
                </div>
            </div>
            <button type="button" class="btn btn-solid">Save Changes</button>
        </div>

        <div class="form-section">
            <h3>Preferences</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="notifications">Email Notifications</label>
                    <select id="notifications">
                        <option>All updates</option>
                        <option>Bookings only</option>
                        <option>None</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="language">Language</label>
                    <select id="language">
                        <option>English</option>
                        <option>Filipino</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Security</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" placeholder="••••••••">
                </div>
            </div>
            <button type="button" class="btn btn-outline">Update Password</button>
        </div>
    </div>
</div>

@endsection
