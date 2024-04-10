<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\QualityIssueController;
use App\Http\Controllers\TrackRecordIssueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Route definition for retrieving user with role.
 *
 * @param string $middleware The middleware to be applied.
 * @param string $url The URL path for the route.
 * @param array $callback The callback function or controller method to be executed.
 * @return void
 */
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUserWithRole']);

/**
 * Registers the routes for user authentication.
 *
 * @param string $url The URL path for the route.
 * @param array $callback The callback function or controller method to be executed.
 * @return void
 */
Route::get('/file/getexcel', [TrackRecordIssueController::class, 'getExcelFromUrl']);
Route::post('/signup', [AuthController::class, 'sign_up']);
Route::post('/signin', [AuthController::class, 'sign_in']);
Route::post('/signout', [AuthController::class, 'sign_out']);

/**
 * Defines a route group that applies the 'auth:sanctum' and 'admin' middleware.
 *
 * @return void
 */
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/manage-information', [InformationController::class, 'store']);

    Route::delete('/manage-information/{information}', [InformationController::class, 'destroy']);
    Route::patch('/manage-information/{information}', [InformationController::class, 'update']);
    Route::prefix('manage-users')->group(function () {
        Route::get('/', [ManageUserController::class, 'getAllUsersWithRoles']);
        Route::post('/change-role', [ManageUserController::class, 'changeUserRole']);
        Route::post('/toggle-approval-status', [ManageUserController::class, 'toggleApprovalStatus']);
    });

    Route::prefix('track-records')->group(function () {
        Route::get('/', [TrackRecordIssueController::class, 'index']);
        Route::get('/{id}', [TrackRecordIssueController::class, 'show']);
        Route::put('/{id}', [TrackRecordIssueController::class, 'update']);
        Route::put('/toggle-closed/{id}', [TrackRecordIssueController::class, 'toggleClosed']);
    });

    Route::prefix('manage-plant')->group(function () {
        Route::get('/', [PlantController::class, 'index']);
        Route::post('/', [PlantController::class, 'store']);
        Route::delete('/{plant}', [PlantController::class, 'destroy']);
        Route::patch('/{plant}', [PlantController::class, 'update']);
    });

    Route::get('/users/count', [ManageUserController::class, 'getUsersCount']);
    Route::get('/issues/count', [IssueController::class, 'getIssuesCount']);
    Route::get('/informations/count', [InformationController::class, 'getInformationsCount']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/user/change-password', [AuthController::class, 'change_password']);

    Route::post('/issue', [IssueController::class, 'store']);
    Route::get('/issue', [IssueController::class, 'index']);
    Route::delete('/issue/{issue}', [IssueController::class, 'destroy']);
    Route::patch('/issue/{issue}', [IssueController::class, 'update']);
    Route::get('/issue-by-date', [IssueController::class, 'getIssuesByDate']);
    Route::get('/issue/{id}/issue-date', [IssueController::class, 'getIssueDateById']);
    Route::get('/issue/{id}/quality-issue', [IssueController::class, 'getIssueAndQualityIssues']);

    Route::get('/issue/{id}/detail', [IssueController::class, 'getIssueDetails']);

    Route::get('/issues-with-quality', [IssueController::class, 'getAllIssuesWithQualityIssuesByDate']);

    Route::post('/quality-issue', [QualityIssueController::class, 'store']);
    Route::get('/quality-issue', [QualityIssueController::class, 'index']);
    Route::get('/quality-issue/get-sugestion/suggestions', [QualityIssueController::class, 'getProblemSuggestions']);
    Route::get('/quality-issue/get-sugestion-solution/suggestions', [QualityIssueController::class, 'getSolutionsSuggestions']);
    Route::get('/quality-issue/get-sugestion-impact/suggestions', [QualityIssueController::class, 'getImpactSuggestions']);
    Route::delete('/quality-issue/{qualityIssue}', [QualityIssueController::class, 'destroy']);
    Route::patch('/quality-issue/{qualityIssue}', [QualityIssueController::class, 'update']);
    Route::get('/quality-issues/{issueId}', [QualityIssueController::class, 'getQualityIssuesByIssueId']);
    Route::get('/quality-issue/{qualityIssue}', [QualityIssueController::class, 'show']);


    Route::put('/edit-user-profile', [ManageUserController::class, 'editUserProfile']);

    Route::get('/manage-information', [InformationController::class, 'index']);
    Route::get('/manage-information/{information}', [InformationController::class, 'show']);

    Route::get('/memo', [MemoController::class, 'index']);
    Route::get('/memo/{issueId}', [MemoController::class, 'getMemosByIssueId']);

    Route::get('/plants/all', [PlantController::class, 'getAllPlants']);
});


Route::middleware(['auth:sanctum', 'manager'])->group(function () {

    Route::post('/memo', [MemoController::class, 'store']);
    Route::patch('/memo/{memo}', [MemoController::class, 'update']);

    Route::delete('/memo/{memo}', [MemoController::class, 'destroy']);
});


