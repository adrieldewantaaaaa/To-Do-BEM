<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));


Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('projects', ProjectController::class);
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('my-tasks', [TaskController::class, 'myTasks'])->name('my-tasks');
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('tasks/{task}/status', TaskStatusController::class)->name('tasks.status');
    Route::post('tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    // Rooms / collaborative groups
    Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::post('rooms/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('rooms/{room}/projects', [RoomController::class, 'storeProject'])->name('rooms.projects.store');
    Route::get('rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::delete('rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::delete('rooms/{room}/members/{user}', [RoomController::class, 'removeMember'])->name('rooms.members.remove');

    Route::get('search', SearchController::class)->name('search');
    Route::get('calendar', CalendarController::class)->name('calendar');
    Route::get('files', FilesController::class)->name('files');
    Route::get('reports', ReportsController::class)->name('reports');
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('settings/appearance', [SettingsController::class, 'update'])->name('settings.appearance');
    Route::patch('settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::get('projects/{project}/export/pdf', [ExportController::class, 'pdf'])->name('projects.export.pdf');
    Route::get('projects/{project}/export/excel', [ExportController::class, 'excel'])->name('projects.export.excel');
});
