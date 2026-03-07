<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

trait WithCrudNotifications
{
    protected function notifySuccess(string $message): void
    {
        $this->dispatch('crud-notify', type: 'success', message: $message);
    }

    protected function notifyError(string $message): void
    {
        $this->dispatch('crud-notify', type: 'error', message: $message);
    }

    protected function validateWithFriendlyMessage(array $rules, array $messages = [], array $attributes = []): array
    {
        try {
            return $this->validate($rules, $messages, $attributes);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->validator->errors()->all())->first();
            $this->notifyError('Data belum bisa diproses. '.($firstError ?: 'Cek lagi isian yang wajib.'));
            throw $exception;
        }
    }
}

