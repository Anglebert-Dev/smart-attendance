<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartAttend')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: #f1f5f9;
            margin: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.4;
            text-decoration: none;
            transition: color .15s, background-color .15s;
            width: 100%;
            cursor: pointer;
            background: none;
            border: none;
            text-align: left;
        }
        .sidebar-link:hover {
            color: #e2e8f0;
            background-color: rgba(255,255,255,.07);
        }
        .sidebar-link.active {
            color: #ffffff;
            background-color: #2563eb;
        }
        .sidebar-link.active:hover { background-color: #2563eb; }

        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.03);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #2563eb;
            color: #ffffff;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.4;
            text-decoration: none;
            transition: background-color .15s, box-shadow .15s;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            box-shadow: 0 2px 8px rgba(37,99,235,.3);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #f8fafc;
            color: #475569;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.4;
            text-decoration: none;
            transition: background-color .15s, color .15s;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            font-family: inherit;
        }
        .btn-secondary:hover { background-color: #f1f5f9; color: #1e293b; }
        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #fef2f2;
            color: #dc2626;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.4;
            text-decoration: none;
            transition: background-color .15s;
            cursor: pointer;
            border: 1px solid #fee2e2;
            font-family: inherit;
        }
        .btn-danger:hover { background-color: #fee2e2; }

        .input {
            display: block;
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 13.5px;
            color: #1e293b;
            background: #ffffff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            font-family: inherit;
            line-height: 1.4;
        }
        .input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        .input::placeholder { color: #94a3b8; }

        .badge-green {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 999px;
            font-size: 11.5px; font-weight: 500;
            background-color: #dcfce7; color: #16a34a;
        }
        .badge-blue {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 999px;
            font-size: 11.5px; font-weight: 500;
            background-color: #dbeafe; color: #2563eb;
        }
        .badge-slate {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 999px;
            font-size: 11.5px; font-weight: 500;
            background-color: #f1f5f9; color: #475569;
        }

        .font-display { font-family: 'Inter', sans-serif; font-weight: 700; }

        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 30;
            backdrop-filter: blur(2px);
        }
        #sidebar-overlay.open { display: block; }

        @media (max-width: 1023px) {
            #sidebar {
                position: fixed !important;
                top: 0; left: 0;
                height: 100%;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform .25s ease;
            }
            #sidebar.open { transform: translateX(0); }
            #hamburger { display: flex !important; }
        }

        @media (min-width: 1024px) {
            #hamburger { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased">

<div id="sidebar-overlay" onclick="closeSidebar()"></div>


<div style="display:flex;height:100vh;overflow:hidden;">

    <aside id="sidebar" style="width:240px;flex-shrink:0;background:#0d1117;border-right:1px solid rgba(255,255,255,.06);display:flex;flex-direction:column;">

        <div style="display:flex;align-items:center;gap:12px;padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#6366f1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:18px;height:18px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p style="color:#f1f5f9;font-size:14px;font-weight:600;letter-spacing:-.01em;line-height:1.2;">SmartAttend</p>
                <p style="color:#475569;font-size:11px;margin-top:2px;">Face Recognition</p>
            </div>
        </div>

        <nav style="flex:1;padding:12px 10px;overflow-y:auto;">

            @if(auth()->user()->role === 'admin')
                <p style="color:#334155;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;padding:0 10px 10px;">Admin</p>

                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.classes.index') }}" class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Classes
                </a>
                <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Students
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Teachers
                </a>
                <a href="{{ route('admin.hods.index') }}" class="sidebar-link {{ request()->routeIs('admin.hods.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Heads of Dept
                </a>
                <a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Attendance
                </a>

                <a href="{{ route('admin.api-keys.index') }}" class="sidebar-link {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    API Keys
                </a>

            @elseif(auth()->user()->role === 'hod')
                <p style="color:#334155;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;padding:0 10px 10px;">HOD Review</p>

                <a href="{{ route('hod.dashboard') }}" class="sidebar-link {{ request()->routeIs('hod.dashboard') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Review Dashboard
                </a>
                <a href="{{ route('hod.teachers.index') }}" class="sidebar-link {{ request()->routeIs('hod.teachers.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Teachers
                </a>
                <a href="{{ route('hod.classes.index') }}" class="sidebar-link {{ request()->routeIs('hod.classes.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Classes
                </a>
                <a href="{{ route('hod.students.index') }}" class="sidebar-link {{ request()->routeIs('hod.students.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Students
                </a>
                <a href="{{ route('hod.attendance.index') }}" class="sidebar-link {{ request()->routeIs('hod.attendance.*') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Attendance
                </a>

            @else
                <p style="color:#334155;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;padding:0 10px 10px;">Teacher</p>

                <a href="{{ route('teacher.dashboard') }}" class="sidebar-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('teacher.classes') }}" class="sidebar-link {{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    My Classes
                </a>
                <a href="{{ route('teacher.students') }}" class="sidebar-link {{ request()->routeIs('teacher.students') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Students
                </a>
                <a href="{{ route('teacher.attendance') }}" class="sidebar-link {{ request()->routeIs('teacher.attendance') ? 'active' : '' }}">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Attendance
                </a>
            @endif
        </nav>

        {{-- User info --}}
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;">
            <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:600;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <p style="color:#e2e8f0;font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3;">{{ auth()->user()->name }}</p>
                <p style="color:#475569;font-size:11px;text-transform:capitalize;margin-top:1px;">{{ auth()->user()->role }}</p>
            </div>
            <div style="width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0;" title="Online"></div>
        </div>

        {{-- Sign out --}}
        <div style="padding:6px 10px 14px;flex-shrink:0;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <main style="flex:1;overflow-y:auto;background:#f1f5f9;display:flex;flex-direction:column;min-width:0;">

        <header style="display:flex;align-items:center;background:#ffffff;border-bottom:1px solid #e2e8f0;padding:0 24px;height:60px;position:sticky;top:0;z-index:20;flex-shrink:0;gap:12px;">

            <button id="hamburger" onclick="openSidebar()" style="display:none;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;cursor:pointer;flex-shrink:0;" aria-label="Open menu">
                <svg style="width:16px;height:16px;color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div style="flex:1;min-width:0;">
                <h1 style="font-size:16px;font-weight:600;color:#0f172a;letter-spacing:-.015em;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">@yield('page-title', 'Dashboard')</h1>
                <p style="font-size:12px;color:#94a3b8;margin-top:1px;line-height:1;">@yield('page-subtitle', '')</p>
            </div>

            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <span style="font-size:12px;color:#94a3b8;">{{ now()->format('D, M d Y') }}</span>
                @yield('header-actions')
            </div>
        </header>

        <div style="padding:20px 24px 0;">
            @if(session('success'))
                <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:10px;font-size:13.5px;margin-bottom:4px;">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="display:flex;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:10px;font-size:13.5px;margin-bottom:4px;">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div style="padding:24px;flex:1;">
            @yield('content')
        </div>
    </main>

</div>

@stack('scripts')
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-overlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
</script>
</body>
</html>
