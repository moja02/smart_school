<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Chat API
Route::post('/chat/send', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Online Exams API
Route::post('/exams/{id}/submit', function(Request $request, $id) {
    return response()->json(['status' => 'success', 'score' => 1]);
});

// Grades API
Route::post('/grades/publish', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Accounting API
Route::post('/accounting/expenses', function(Request $request) {
    return response()->json(['status' => 'success']);
});

// Admission API
Route::post('/admissions/{id}/approve', function(Request $request, $id) {
    return response()->json(['status' => 'success']);
});

// Manager Settings API
Route::post('/manager/settings', function(Request $request) {
    return response()->json(['status' => 'success']);
});
