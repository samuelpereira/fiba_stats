<?php

namespace App\Livewire\Concerns;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

trait ConfirmsDeletion
{
    public int|string|null $recordIdToDelete = null;

    abstract protected function deletionModalName(): string;

    abstract protected function recordForDeletion(int|string $key): Model;

    abstract protected function performDeletion(Model $record): void;

    abstract protected function deletedMessage(): string;

    abstract protected function clearListedRecords(): void;

    public function confirmDelete(int|string $key): void
    {
        $record = $this->recordForDeletion($key);

        $this->authorizeDeletion($record);

        $routeKey = $record->getRouteKey();

        $this->recordIdToDelete = is_int($routeKey) ? $routeKey : (string) $routeKey;

        Flux::modal($this->deletionModalName())->show();
    }

    public function deleteConfirmed(): void
    {
        if ($this->recordIdToDelete === null) {
            return;
        }

        $record = $this->recordForDeletion($this->recordIdToDelete);

        $this->authorizeDeletion($record);

        try {
            $this->performDeletion($record);
        } catch (ValidationException $exception) {
            Flux::modal($this->deletionModalName())->close();

            Flux::toast(variant: 'danger', text: (string) $exception->validator->errors()->first());

            return;
        }

        $this->recordIdToDelete = null;
        $this->clearListedRecords();

        Flux::modal($this->deletionModalName())->close();

        Flux::toast(variant: 'success', text: $this->deletedMessage());
    }

    protected function authorizeDeletion(Model $record): void
    {
        $this->authorize('delete', $record);
    }

    #[Computed]
    public function recordToDelete(): ?Model
    {
        if ($this->recordIdToDelete === null) {
            return null;
        }

        return $this->recordForDeletion($this->recordIdToDelete);
    }
}
