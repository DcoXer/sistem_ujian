<div class="space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Hubungkan Guru ke Mapel & Kelas</h3>
        <form wire:submit="createTeacherSubject" class="mt-4 grid gap-4 md:grid-cols-3">
            <select wire:model="teacher_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Pilih Guru</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                @endforeach
            </select>
            <select wire:model="subject_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Pilih Mapel</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                @endforeach
            </select>
            <select wire:model="class_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Pilih Kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}{{ $class->schoolYear?->name ? ' - '.$class->schoolYear->name : '' }}</option>
                @endforeach
            </select>
            <div class="md:col-span-3">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
        @error('teacher_id') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        @error('subject_id') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        @error('class_id') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Input Wali Kelas</h3>
        <form wire:submit="createHomeroom" class="mt-4 grid gap-4 md:grid-cols-2">
            <select wire:model="homeroom_teacher_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Pilih Guru</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                @endforeach
            </select>
            <select wire:model="homeroom_class_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Pilih Kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <div class="md:col-span-2">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan Wali Kelas</button>
            </div>
        </form>
        @error('homeroom_teacher_id') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        @error('homeroom_class_id') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h4 class="text-sm font-semibold text-slate-900">Daftar Guru Mapel</h4>
            <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full min-w-[520px] text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-medium">Guru</th>
                            <th class="px-3 py-2 font-medium">Mapel</th>
                            <th class="px-3 py-2 font-medium">Kelas</th>
                            <th class="px-3 py-2 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacherSubjects as $item)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 text-slate-900">{{ $item->teacher?->name }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $item->subject?->name }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $item->schoolClass?->name }}</td>
                                <td class="px-3 py-2">
                                    <button wire:click="deleteTeacherSubject({{ $item->id }})" data-confirm-message="Hapus assignment ini?" class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-slate-500">Belum ada assignment mapel.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $teacherSubjects->links() }}
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h4 class="text-sm font-semibold text-slate-900">Daftar Wali Kelas</h4>
            <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full min-w-[520px] text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-medium">Kelas</th>
                            <th class="px-3 py-2 font-medium">Wali</th>
                            <th class="px-3 py-2 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($homerooms as $item)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 text-slate-900">{{ $item->schoolClass?->name }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $item->teacher?->name }}</td>
                                <td class="px-3 py-2">
                                    <button wire:click="deleteHomeroom({{ $item->id }})" data-confirm-message="Hapus assignment wali ini?" class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-4 text-center text-slate-500">Belum ada assignment wali.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $homerooms->links() }}
            </div>
        </article>
    </section>
</div>
