@extends('layouts.app')

@section('title', 'API Keys')
@section('page-title', 'API Keys')
@section('page-subtitle', 'Manage access keys for the Python AI module')

@section('content')

{{-- ── New key just generated (shown once) ───────────────────────── --}}
@if(session('new_key'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:20px 24px;margin-bottom:20px;">
    <div style="display:flex;align-items:flex-start;gap:12px;">
        <div style="width:32px;height:32px;background:#dcfce7;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
            <svg style="width:16px;height:16px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <div style="flex:1;min-width:0;">
            <p style="font-size:14px;font-weight:600;color:#15803d;margin-bottom:4px;">
                Key created: {{ session('new_key_name') }}
            </p>
            <p style="font-size:12px;color:#166534;margin-bottom:12px;">
                Copy this key now — it will <strong>never be shown again</strong> once you leave this page.
            </p>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <code id="new-key-val" style="background:#dcfce7;border:1px solid #86efac;padding:10px 14px;border-radius:8px;font-size:13px;font-family:monospace;word-break:break-all;flex:1;color:#14532d;letter-spacing:.02em;">{{ session('new_key') }}</code>
                <button onclick="copyKey()" style="display:flex;align-items:center;gap:6px;background:#16a34a;color:#fff;border:none;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap;">
                    <svg id="copy-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="copy-label">Copy</span>
                </button>
            </div>
            <p style="font-size:11.5px;color:#166534;margin-top:10px;">
                Paste this into <code style="background:#bbf7d0;padding:1px 6px;border-radius:4px;">python-ai/.env</code> as <code style="background:#bbf7d0;padding:1px 6px;border-radius:4px;">API_KEY=...</code>
            </p>
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:10px;font-size:13.5px;margin-bottom:16px;">
    <svg style="width:15px;height:15px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Generate new key form ──────────────────────────────────── --}}
    <div class="card">
        <h3 style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:4px;">Generate New Key</h3>
        <p style="font-size:12px;color:#94a3b8;margin-bottom:16px;">Give it a descriptive name so you know which device uses it.</p>

        <form method="POST" action="{{ route('admin.api-keys.store') }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">
                    Key Name <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="name" required
                    class="input" placeholder="e.g. Raspberry Pi — Room A"
                    value="{{ old('name') }}">
                @error('name')
                    <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate Key
            </button>
        </form>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;">
            <p style="font-size:11.5px;color:#94a3b8;line-height:1.6;">
                <strong style="color:#64748b;">Setup:</strong><br>
                1. Generate a key here<br>
                2. Copy the key (shown once)<br>
                3. Add to <code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;">python-ai/.env</code>:<br>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">API_KEY=sas_...</code>
            </p>
        </div>
    </div>

    {{-- ── Existing keys table ─────────────────────────────────────── --}}
    <div class="card lg:col-span-2">
        <h3 style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">
            Active Keys
            <span style="font-size:12px;font-weight:400;color:#94a3b8;margin-left:6px;">{{ $keys->count() }} total</span>
        </h3>

        @if($keys->count())
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="w-full" style="min-width:480px;">
                <thead>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:10px;">Name</th>
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:10px;">Status</th>
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:10px;">Last Used</th>
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:10px;">Created</th>
                        <th style="text-align:right;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keys as $key)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:12px 0;">
                            <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $key->name }}</p>
                            @if($key->plain_key)
                                <p style="font-size:11px;color:#f59e0b;margin-top:2px;">⚠ Not yet used — plain key still stored</p>
                            @endif
                        </td>
                        <td style="padding:12px 0;">
                            @if($key->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span style="display:inline-flex;align-items:center;padding:2px 10px;border-radius:999px;font-size:11.5px;font-weight:500;background:#fee2e2;color:#dc2626;">Revoked</span>
                            @endif
                        </td>
                        <td style="padding:12px 0;font-size:12.5px;color:#64748b;">
                            {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                        </td>
                        <td style="padding:12px 0;font-size:12.5px;color:#64748b;white-space:nowrap;">
                            {{ $key->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 0;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                @if($key->is_active)
                                    <form method="POST" action="{{ route('admin.api-keys.revoke', $key) }}"
                                          onsubmit="return confirm('Revoke key \'{{ $key->name }}\'? The Python module will stop working immediately.')">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:6px 12px;font-size:12px;">
                                            Revoke
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.api-keys.destroy', $key) }}"
                                      onsubmit="return confirm('Permanently delete this key?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding:6px 12px;font-size:12px;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            <div style="text-align:center;padding:48px 0;color:#94a3b8;">
                <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <p style="font-size:13.5px;font-weight:500;color:#475569;margin-bottom:4px;">No API keys yet</p>
                <p style="font-size:12.5px;">Generate your first key using the form on the left.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function copyKey() {
    const val = document.getElementById('new-key-val').textContent.trim();
    navigator.clipboard.writeText(val).then(() => {
        document.getElementById('copy-label').textContent = 'Copied!';
        document.getElementById('copy-icon').innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>`;
        setTimeout(() => {
            document.getElementById('copy-label').textContent = 'Copy';
            document.getElementById('copy-icon').innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>`;
        }, 2500);
    });
}
</script>
@endpush
