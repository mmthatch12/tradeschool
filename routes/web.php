<?php

use App\Livewire\ApplicationForm;
use App\Livewire\ApplicationStatus;
use App\Livewire\StudentPortal;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/apply', ApplicationForm::class)->name('apply');
Route::get('/apply/{token}/status', ApplicationStatus::class)->name('application.status');
Route::get('/portal/{token}', StudentPortal::class)->name('portal');
