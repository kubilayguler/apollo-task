<?php

declare(strict_types=1);

use App\Http\Controllers\AiAssistantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('ai')->group(function () {
    // Text-to-Action: user types a natural-language command.
    Route::post('/text-command', [AiAssistantController::class, 'processTextCommand'])
        ->name('ai.text-command');

    // Contextual Action: user clicks "AI Edit / Break Down" on an existing task.
    Route::post('/tasks/{task}/action', [AiAssistantController::class, 'processContextualAction'])
        ->name('ai.task-action');
});
