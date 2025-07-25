<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TimetablesController;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Dashboard
Route::get('/dashboard', 'DashboardController@index');

// Resource Controllers
Route::resources([
    'rooms' => 'RoomsController',
    'courses' => 'CoursesController',
    'timeslots' => 'TimeslotsController',
    'professors' => 'ProfessorsController',
    'classes' => 'CollegeClassesController',
]);

// Timetables
Route::post('timetables', 'TimetablesController@store');
Route::get('timetables', 'TimetablesController@index');
Route::get('timetables/view/{id}', 'TimetablesController@view');
Route::get('/admin/export-timetable', [TimetablesController::class, 'export'])->name('timetable.export');

// User Account Activation
Route::get('/users/activate', 'UsersController@showActivationPage');
Route::post('/users/activate', 'UsersController@activateUser');

// Home
Route::get('/home', 'HomeController@index')->name('home');

// Account & Auth
Route::get('/login', 'UsersController@showLoginPage');
Route::post('/login', 'UsersController@loginUser');
Route::get('/request_reset', 'UsersController@showPasswordRequestPage');
Route::post('/request_reset', 'UsersController@requestPassword');
Route::get('/reset_password', 'UsersController@showResetPassword');
Route::post('/reset_password', 'UsersController@resetPassword');
Route::get('/my_account', 'UsersController@showAccountPage');
Route::post('/my_account', 'UsersController@updateAccount');

// Logout
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
});
