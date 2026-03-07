<div wire:poll.10s class="space-y-3">
    @error('start')
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
    @enderror

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[980px] text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Ujian</th>
                    <th class="px-4 py-3 font-medium">Jadwal</th>
                    <th class="px-4 py-3 font-medium">Status Attempt</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    @php
                        $isNotStartedYet = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED && $exam->ui_state === \App\Support\ExamUiState::NOT_STARTED;
                        $isEnded = in_array($exam->ui_action, [\App\Support\ExamUiAction::START_DISABLED, \App\Support\ExamUiAction::CONTINUE_DISABLED], true) && $exam->ui_state === \App\Support\ExamUiState::FINISHED;
                        $cannotStart = $exam->ui_action === \App\Support\ExamUiAction::START_DISABLED;
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $exam->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-xs uppercase text-slate-500">{{ $exam->my_attempt_status ?? 'belum mulai' }}</td>
                        <td class="px-4 py-3">
                            @if ($exam->my_attempt_id)
                                @if ($exam->ui_action === \App\Support\ExamUiAction::RESULT)
                                    <a href="{{ route('student.exams.result', $exam->my_attempt_id) }}" class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Lihat Hasil</a>
                                @elseif ($exam->ui_action === \App\Support\ExamUiAction::CONTINUE_ENABLED)
                                    <a href="{{ route('student.exams.show', $exam->my_attempt_id) }}" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700">Lanjut Ujian</a>
                                @else
                                    <button type="button" disabled class="cursor-not-allowed rounded-lg bg-slate-400 px-3 py-1 text-xs font-semibold text-white">
                                        {{ $exam->ui_action === \App\Support\ExamUiAction::WAITING_RESULT ? 'Menunggu Hasil' : 'Lanjut Ujian' }}
                                    </button>
                                    @if ($exam->ui_message)
                                        <p class="mt-1 text-xs {{ ($exam->ui_message_tone ?? '') === 'amber' ? 'text-amber-700' : 'text-rose-700' }}">{{ $exam->ui_message }}</p>
                                    @endif
                                @endif
                            @else
                                <button
                                    type="button"
                                    wire:click="startExam({{ $exam->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="startExam"
                                    {{ $cannotStart ? 'disabled' : '' }}
                                    class="rounded-lg px-3 py-1 text-xs font-semibold text-white {{ $cannotStart ? 'cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700' }}"
                                >
                                    Start Exam
                                </button>
                                @if ($exam->ui_message)
                                    <p class="mt-1 text-xs {{ $isNotStartedYet ? 'text-amber-700' : ($isEnded ? 'text-rose-700' : 'text-slate-600') }}">{{ $exam->ui_message }}</p>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada ujian yang dipublish.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
