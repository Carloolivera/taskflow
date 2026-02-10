<?php

use App\Livewire\Dashboard;
use App\Livewire\ProjectManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/projects', ProjectManager::class)
    ->middleware(['auth'])
    ->name('projects.index');

Route::get('/projects/{project}/tasks', \App\Livewire\TaskManager::class)
    ->middleware(['auth'])
    ->name('tasks.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin only
Route::get('/tags', \App\Livewire\TagManager::class)
    ->middleware(['auth', 'admin'])
    ->name('tags.index');

require __DIR__.'/auth.php';
