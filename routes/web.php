<?php

use App\Livewire\Administrators\Create as CreateAdministrator;
use App\Livewire\Administrators\Edit as EditAdministrator;
use App\Livewire\Administrators\Index as AdministratorIndex;
use App\Livewire\Clubs\Create as CreateClub;
use App\Livewire\Clubs\Edit as EditClub;
use App\Livewire\Clubs\Index as ClubIndex;
use App\Livewire\Users\Create as CreateUser;
use App\Livewire\Users\Edit as EditUser;
use App\Livewire\Users\Index as UserIndex;
use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('administrators', AdministratorIndex::class)->name('administrators.index')->can('viewAnyStaff', User::class);
    Route::livewire('administrators/create', CreateAdministrator::class)->name('administrators.create')->can('createStaff', User::class);
    Route::livewire('administrators/{user}/edit', EditAdministrator::class)->name('administrators.edit')->can('updateStaff', 'user');

    Route::livewire('clubs', ClubIndex::class)->name('clubs.index')->can('viewAny', Club::class);
    Route::livewire('clubs/create', CreateClub::class)->name('clubs.create')->can('create', Club::class);
    Route::livewire('clubs/{club}/edit', EditClub::class)->name('clubs.edit')->can('update', 'club');

    Route::livewire('users', UserIndex::class)->name('users.index')->can('viewAny', User::class);
    Route::livewire('users/create', CreateUser::class)->name('users.create')->can('create', User::class);
    Route::livewire('users/{user}/edit', EditUser::class)->name('users.edit')->can('update', 'user');
});

require __DIR__.'/settings.php';
