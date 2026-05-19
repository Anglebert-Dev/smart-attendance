@props(['teachers', 'maxVisible' => 3, 'placement' => 'above'])

@php
    $teachers = $teachers instanceof \Illuminate\Support\Collection ? $teachers : collect($teachers);
    $visible = $teachers->take($maxVisible);
    $extra = max($teachers->count() - $maxVisible, 0);

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }
        return strtoupper(mb_substr($name, 0, 2));
    };

    $avatarColors = ['bg-violet-100 text-violet-700', 'bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-cyan-100 text-cyan-700'];
@endphp

@once
    <style>
        .teacher-avatars-details > summary { outline: none; }
        .teacher-avatars-details[open] > summary { opacity: 0.85; }
        .teacher-avatars-popover {
            display: none;
            position: fixed;
            z-index: 100;
            width: 14rem;
        }
        .teacher-avatars-details[open] .teacher-avatars-popover {
            display: block;
        }
    </style>
    @push('scripts')
    <script>
        function positionTeacherAvatarsPopover(details) {
            const popover = details.querySelector('.teacher-avatars-popover');
            const summary = details.querySelector('summary');
            if (!popover || !summary) return;

            const rect = summary.getBoundingClientRect();
            const gap = 8;
            const preferAbove = details.dataset.placement !== 'below';

            popover.style.left = '0px';
            popover.style.top = '0px';

            const width = popover.offsetWidth;
            const height = popover.offsetHeight;

            let left = rect.left;
            let top = preferAbove ? rect.top - height - gap : rect.bottom + gap;

            if (preferAbove && top < gap) {
                top = rect.bottom + gap;
            } else if (!preferAbove && top + height > window.innerHeight - gap) {
                top = rect.top - height - gap;
            }

            left = Math.max(gap, Math.min(left, window.innerWidth - width - gap));

            popover.style.left = left + 'px';
            popover.style.top = top + 'px';
        }

        function closeTeacherAvatarsPopovers(except) {
            document.querySelectorAll('.teacher-avatars-details[open]').forEach(function (el) {
                if (el !== except) el.removeAttribute('open');
            });
        }

        document.addEventListener('toggle', function (e) {
            if (!e.target.classList.contains('teacher-avatars-details')) return;

            if (e.target.open) {
                closeTeacherAvatarsPopovers(e.target);
                requestAnimationFrame(function () {
                    positionTeacherAvatarsPopover(e.target);
                });
            }
        }, true);

        window.addEventListener('resize', function () {
            document.querySelectorAll('.teacher-avatars-details[open]').forEach(positionTeacherAvatarsPopover);
        });

        document.addEventListener('scroll', function () {
            document.querySelectorAll('.teacher-avatars-details[open]').forEach(positionTeacherAvatarsPopover);
        }, true);

        document.addEventListener('click', function (e) {
            if (e.target.closest('.teacher-avatars-details')) return;
            closeTeacherAvatarsPopovers(null);
        });
    </script>
    @endpush
@endonce

@if($teachers->isEmpty())
    <span class="text-xs text-slate-400 italic">Not assigned</span>
@else
    <details class="teacher-avatars-details relative inline-block" data-placement="{{ $placement }}">
        <summary class="list-none cursor-pointer flex items-center gap-2.5 min-w-0 [&::-webkit-details-marker]:hidden">
            <span class="flex items-center -space-x-2 shrink-0">
                @foreach($visible as $index => $teacher)
                    <span
                        class="inline-flex w-7 h-7 rounded-full border-2 border-white items-center justify-center text-[10px] font-bold ring-1 ring-slate-100 {{ $avatarColors[$index % count($avatarColors)] }}"
                        title="{{ $teacher->name }}"
                    >{{ $initials($teacher->name) }}</span>
                @endforeach
                @if($extra > 0)
                    <span class="inline-flex w-7 h-7 rounded-full border-2 border-white bg-slate-100 text-slate-600 items-center justify-center text-[10px] font-semibold ring-1 ring-slate-200">
                        +{{ $extra }}
                    </span>
                @endif
            </span>
            <span class="text-xs text-slate-600 truncate max-w-[140px] sm:max-w-[200px]">
                {{ $teachers->first()->name }}@if($teachers->count() > 1)<span class="text-slate-400"> +{{ $teachers->count() - 1 }}</span>@endif
            </span>
        </summary>

        <div
            class="teacher-avatars-popover rounded-lg border border-slate-200 bg-white py-2 shadow-lg shadow-slate-200/60"
            role="dialog"
            aria-label="Assigned teachers"
        >
            <p class="px-3 pb-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                {{ $teachers->count() }} {{ Str::plural('teacher', $teachers->count()) }}
            </p>
            <ul class="max-h-48 overflow-y-auto">
                @foreach($teachers as $index => $teacher)
                    <li class="flex items-center gap-2.5 px-3 py-1.5 hover:bg-slate-50">
                        <span class="inline-flex w-6 h-6 shrink-0 rounded-full items-center justify-center text-[9px] font-bold {{ $avatarColors[$index % count($avatarColors)] }}">
                            {{ $initials($teacher->name) }}
                        </span>
                        <span class="text-sm text-slate-700 truncate" title="{{ $teacher->name }}">{{ $teacher->name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </details>
@endif
