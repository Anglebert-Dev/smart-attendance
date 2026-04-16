<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SmartAttend</title>
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
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        .grid-bg {
            background-image: linear-gradient(rgba(37,99,235,0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(37,99,235,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 grid-bg flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-white text-3xl font-bold" style="letter-spacing:-.02em;">SmartAttend</h1>
            <p class="text-slate-400 text-sm mt-1">AI-Powered Attendance System</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <h2 class="text-white font-semibold text-lg mb-6">Sign in to your account</h2>

            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-slate-400 text-xs font-medium uppercase tracking-wider block mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-500 transition-all"
                        placeholder="you@school.edu">
                </div>
                <div>
                    <label class="text-slate-400 text-xs font-medium uppercase tracking-wider block mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-500 transition-all"
                        placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-slate-400 text-sm cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-600 bg-slate-700 text-blue-500">
                        Remember me
                    </label>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-medium text-sm transition-all duration-200 mt-2">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            Smart Attendance System &copy; {{ date('Y') }}
        </p>
    </div>
</body>
</html>
