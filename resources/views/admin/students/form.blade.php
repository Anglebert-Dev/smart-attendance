@extends('layouts.app')

@section('title', isset($student) ? 'Edit Student' : 'Add Student')
@section('page-title', isset($student) ? 'Edit Student' : 'Add Student')
@section('page-subtitle', isset($student) ? 'Update student details' : 'Enroll a new student')

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST"
            action="{{ isset($student) ? route('admin.students.update', $student) : route('admin.students.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if(isset($student)) @method('PUT') @endif

            <div class="space-y-5">

                {{-- Name + Student ID --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}" required
                            class="input" placeholder="John Doe">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Student ID <span class="text-red-500">*</span></label>
                        <input type="text" name="student_id" value="{{ old('student_id', $student->student_id ?? '') }}" required
                            class="input" placeholder="STU-001">
                        @error('student_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}"
                        class="input" placeholder="student@school.edu">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Class --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Assign to Class <span class="text-red-500">*</span></label>
                    <select name="class_id" class="input" required>
                        <option value="">— Select a class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ old('class_id', $student->class_id ?? '') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} {{ $class->teacher ? '(Teacher: ' . $class->teacher->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Photos --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Face Recognition Photos
                        <span class="text-slate-400 font-normal text-xs">(up to 10 — more = better accuracy)</span>
                    </label>

                    {{-- Existing photos on edit --}}
                    @if(isset($student) && $student->photos->count())
                        <div class="mb-3">
                            <p class="text-xs text-slate-500 mb-2">Saved photos — click × to remove</p>
                            <div id="existing-grid" style="display:flex;flex-wrap:wrap;gap:8px;">
                                @foreach($student->photos as $photo)
                                <div style="position:relative;width:80px;height:80px;flex-shrink:0;">
                                    <img src="{{ $photo->url() }}"
                                        style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #e2e8f0;">
                                    @if($student->photo === $photo->path)
                                        <span style="position:absolute;bottom:4px;left:50%;transform:translateX(-50%);background:#2563eb;color:#fff;font-size:9px;font-weight:600;padding:1px 6px;border-radius:4px;white-space:nowrap;">AVATAR</span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.students.photos.destroy', [$student, $photo]) }}"
                                          style="position:absolute;top:-6px;right:-6px;"
                                          onsubmit="return confirm('Remove this photo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            style="width:20px;height:20px;border-radius:50%;background:#ef4444;color:#fff;border:none;cursor:pointer;font-size:12px;line-height:1;display:flex;align-items:center;justify-content:center;"
                                            title="Remove">×</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Upload zone --}}
                    <div id="drop-zone"
                        onclick="document.getElementById('photo-input').click()"
                        style="border:2px dashed #cbd5e1;border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;"
                        ondragover="event.preventDefault();this.style.borderColor='#3b82f6';this.style.background='#eff6ff';"
                        ondragleave="this.style.borderColor='#cbd5e1';this.style.background='';"
                        ondrop="handleDrop(event)">
                        <svg style="width:28px;height:28px;color:#94a3b8;margin:0 auto 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p style="font-size:13.5px;color:#475569;font-weight:500;">Click or drag photos here</p>
                        <p style="font-size:11.5px;color:#94a3b8;margin-top:4px;">JPG, PNG up to 5MB each — front-facing, good lighting</p>
                        <input id="photo-input" type="file" name="photos[]"
                            accept="image/*" multiple style="display:none;"
                            onchange="previewPhotos(this.files)">
                    </div>
                    @error('photos')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('photos.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                    {{-- New photo previews --}}
                    <div id="preview-grid" style="display:none;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
                </div>

            </div>

            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isset($student) ? 'Update Student' : 'Add Student' }}
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewPhotos(files) {
    const grid = document.getElementById('preview-grid');
    grid.innerHTML = '';

    if (!files.length) { grid.style.display = 'none'; return; }

    grid.style.display = 'flex';

    Array.from(files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;width:80px;height:80px;flex-shrink:0;';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #bfdbfe;';

            if (i === 0) {
                const badge = document.createElement('span');
                badge.textContent = 'AVATAR';
                badge.style.cssText = 'position:absolute;bottom:4px;left:50%;transform:translateX(-50%);background:#2563eb;color:#fff;font-size:9px;font-weight:600;padding:1px 6px;border-radius:4px;white-space:nowrap;';
                wrap.appendChild(badge);
            }

            wrap.appendChild(img);
            grid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });

    // Update drop zone text
    document.getElementById('drop-zone').querySelector('p').textContent =
        `${files.length} photo${files.length > 1 ? 's' : ''} selected`;
}

function handleDrop(e) {
    e.preventDefault();
    const zone = e.currentTarget;
    zone.style.borderColor = '#cbd5e1';
    zone.style.background = '';

    const input = document.getElementById('photo-input');
    const dt = e.dataTransfer;

    // Assign files to the input via DataTransfer
    const transfer = new DataTransfer();
    Array.from(dt.files).forEach(f => {
        if (f.type.startsWith('image/')) transfer.items.add(f);
    });
    input.files = transfer.files;
    previewPhotos(input.files);
}
</script>
@endpush
