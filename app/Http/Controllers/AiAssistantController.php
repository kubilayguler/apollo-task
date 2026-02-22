<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\TodoAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Handles AI-powered task management endpoints.
 *
 * - processTextCommand()      → Text-to-Action (natural language → tasks)
 * - processContextualAction() → Button-click on existing task (edit/split/schedule)
 */
final class AiAssistantController extends Controller
{
    public function __construct(
        private readonly TodoAiService $aiService,
    ) {}

    // ──────────────────────────────────────────────────────────────
    //  1. Text-to-Action
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/ai/text-command
     *
     * Accept a free-text command, send it to the AI, then execute
     * the returned action against the database.
     */
    public function processTextCommand(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:1000'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        try {
            $aiResult = $this->aiService->processTextCommand($validated['command']);

            $entities = DB::transaction(
                fn () => $this->executeAction(
                    action: $aiResult['action'],
                    parameters: $aiResult['parameters'],
                    projectId: $validated['project_id'] ?? null,
                    userId: (int) auth()->id(),
                )
            );

            return response()->json([
                'success' => true,
                'action' => $aiResult['action'],
                'message' => $aiResult['feedback_message'],
                'data' => $entities,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('AiAssistantController::processTextCommand unexpected error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  2. Contextual Action (on existing task)
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/ai/tasks/{task}/action
     *
     * Perform an AI-driven action on an existing task (split, schedule, etc.).
     */
    public function processContextualAction(Request $request, Task $task): JsonResponse
    {
        // Authorize: task must belong to the authenticated user.
        abort_unless($task->project->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'action_hint' => ['nullable', 'string', 'in:break_down,auto_schedule,optimize,general'],
            'extra_context' => ['nullable', 'string', 'max:500'],
        ]);

        $actionHint = $validated['action_hint'] ?? 'general';
        $extraContext = $validated['extra_context'] ?? '';

        try {
            // Eager-load subtasks for context.
            $task->load('subtasks');

            $aiResult = $this->aiService->processContextualAction($task, $actionHint, $extraContext);

            $entities = DB::transaction(
                fn () => $this->executeContextualAction(
                    task: $task,
                    action: $aiResult['action'],
                    parameters: $aiResult['parameters'],
                )
            );

            return response()->json([
                'success' => true,
                'action' => $aiResult['action'],
                'message' => $aiResult['feedback_message'],
                'data' => $entities,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('AiAssistantController::processContextualAction unexpected error', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Action Executors
    // ──────────────────────────────────────────────────────────────

    /**
     * Route an AI action to the appropriate handler (text-command flow).
     *
     * @return array<string, mixed> Created/updated entities for the JSON response.
     */
    private function executeAction(string $action, array $parameters, ?int $projectId, int $userId): array
    {
        return match ($action) {
            'create_tasks' => $this->handleCreateTasks($parameters, $projectId, $userId),
            default => throw new RuntimeException("Action \"{$action}\" is not supported in text-command mode."),
        };
    }

    /**
     * Route an AI action to the appropriate handler (contextual flow).
     *
     * @return array<string, mixed>
     */
    private function executeContextualAction(Task $task, string $action, array $parameters): array
    {
        return match ($action) {
            'split_existing_task' => $this->handleSplitTask($task, $parameters),
            'auto_schedule' => $this->handleAutoSchedule($task, $parameters),
            'optimize_description' => $this->handleOptimizeDescription($task, $parameters),
            'update_task' => $this->handleUpdateTask($task, $parameters),
            'create_tasks' => $this->handleCreateTasksForExisting($task, $parameters),
            default => throw new RuntimeException("Action \"{$action}\" is not supported for contextual edits."),
        };
    }

    // ──────────────────────────────────────────────────────────────
    //  Handlers
    // ──────────────────────────────────────────────────────────────

    /**
     * Create new tasks (and optional sub-tasks) under a project.
     */
    private function handleCreateTasks(array $params, ?int $projectId, int $userId): array
    {
        // Resolve or create the target project.
        $project = $this->resolveProject($params, $projectId, $userId);

        $createdTasks = [];

        foreach ($params['tasks'] ?? [] as $index => $taskData) {
            $task = $project->tasks()->create([
                'title' => $taskData['title'],
                'content' => $taskData['content'] ?? null,
                'priority' => $this->sanitizePriority($taskData['priority'] ?? 'none'),
                'status' => $this->sanitizeStatus($taskData['status'] ?? 'todo'),
                'due_date' => $this->parseDate($taskData['due_date'] ?? null),
                'order' => $index,
            ]);

            // Create sub-tasks if present.
            $subtasks = [];
            foreach ($taskData['subtasks'] ?? [] as $stIndex => $subtaskData) {
                $subtasks[] = $task->subtasks()->create([
                    'project_id' => $project->id,
                    'title' => $subtaskData['title'],
                    'content' => $subtaskData['content'] ?? null,
                    'priority' => $this->sanitizePriority($subtaskData['priority'] ?? 'none'),
                    'status' => $this->sanitizeStatus($subtaskData['status'] ?? 'todo'),
                    'due_date' => $this->parseDate($subtaskData['due_date'] ?? null),
                    'order' => $stIndex,
                ]);
            }

            $task->load('subtasks');
            $createdTasks[] = $task;
        }

        return [
            'project' => $project->only('id', 'name', 'slug'),
            'tasks' => collect($createdTasks)->map(fn (Task $t) => $t->toArray())->toArray(),
        ];
    }

    /**
     * Split an existing task into sub-tasks.
     */
    private function handleSplitTask(Task $task, array $params): array
    {
        // Optionally update the parent task's title/content.
        $updates = array_filter([
            'title' => $params['updated_title'] ?? null,
            'content' => $params['updated_content'] ?? null,
        ]);

        if ($updates) {
            $task->update($updates);
        }

        $created = [];
        foreach ($params['subtasks'] ?? [] as $index => $subtaskData) {
            $created[] = $task->subtasks()->create([
                'project_id' => $task->project_id,
                'title' => $subtaskData['title'],
                'content' => $subtaskData['content'] ?? null,
                'priority' => $this->sanitizePriority($subtaskData['priority'] ?? $task->priority),
                'status' => $this->sanitizeStatus($subtaskData['status'] ?? 'todo'),
                'due_date' => $this->parseDate($subtaskData['due_date'] ?? null),
                'order' => $index,
            ]);
        }

        $task->refresh()->load('subtasks');

        return [
            'task' => $task->toArray(),
        ];
    }

    /**
     * Auto-schedule a task (and optionally create/update sub-tasks with dates).
     */
    private function handleAutoSchedule(Task $task, array $params): array
    {
        $task->update([
            'due_date' => $this->parseDate($params['due_date'] ?? null),
        ]);

        // Schedule sub-tasks — match by title to existing or create new ones.
        foreach ($params['subtasks'] ?? [] as $index => $stData) {
            $existing = $task->subtasks()->where('title', $stData['title'])->first();

            if ($existing) {
                $existing->update([
                    'due_date' => $this->parseDate($stData['due_date'] ?? null),
                ]);
            } else {
                $task->subtasks()->create([
                    'project_id' => $task->project_id,
                    'title' => $stData['title'],
                    'due_date' => $this->parseDate($stData['due_date'] ?? null),
                    'priority' => $task->priority,
                    'status' => 'todo',
                    'order' => $index,
                ]);
            }
        }

        $task->refresh()->load('subtasks');

        return [
            'task' => $task->toArray(),
        ];
    }

    /**
     * Optimize/rewrite a task's title and content.
     */
    private function handleOptimizeDescription(Task $task, array $params): array
    {
        $task->update(array_filter([
            'title' => $params['updated_title'] ?? null,
            'content' => $params['updated_content'] ?? null,
        ]));

        $task->refresh();

        return [
            'task' => $task->toArray(),
        ];
    }

    /**
     * General-purpose task field update.
     */
    private function handleUpdateTask(Task $task, array $params): array
    {
        $fields = array_filter([
            'title' => $params['title'] ?? null,
            'content' => $params['content'] ?? null,
            'priority' => isset($params['priority']) ? $this->sanitizePriority($params['priority']) : null,
            'status' => isset($params['status']) ? $this->sanitizeStatus($params['status']) : null,
            'due_date' => $this->parseDate($params['due_date'] ?? null),
        ]);

        if ($fields) {
            $task->update($fields);
        }

        $task->refresh();

        return [
            'task' => $task->toArray(),
        ];
    }

    /**
     * Handle the edge case where AI returns create_tasks in contextual mode:
     * treat them as sub-tasks of the existing task.
     */
    private function handleCreateTasksForExisting(Task $task, array $params): array
    {
        $created = [];
        foreach ($params['tasks'] ?? [] as $index => $taskData) {
            $created[] = $task->subtasks()->create([
                'project_id' => $task->project_id,
                'title' => $taskData['title'],
                'content' => $taskData['content'] ?? null,
                'priority' => $this->sanitizePriority($taskData['priority'] ?? 'none'),
                'status' => $this->sanitizeStatus($taskData['status'] ?? 'todo'),
                'due_date' => $this->parseDate($taskData['due_date'] ?? null),
                'order' => $index,
            ]);
        }

        $task->refresh()->load('subtasks');

        return [
            'task' => $task->toArray(),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Resolve the target project — use the supplied ID, or find/create by AI-suggested name.
     */
    private function resolveProject(array $params, ?int $projectId, int $userId): Project
    {
        if ($projectId) {
            $project = Project::where('id', $projectId)
                ->where('user_id', $userId)
                ->firstOrFail();

            return $project;
        }

        $projectName = $params['project_name'] ?? 'General';

        return Project::firstOrCreate(
            ['user_id' => $userId, 'slug' => Str::slug($projectName)],
            [
                'name' => $projectName,
                'description' => "Auto-created by AI assistant.",
            ],
        );
    }

    private function sanitizePriority(string $value): string
    {
        return in_array($value, ['none', 'low', 'medium', 'high'], true) ? $value : 'none';
    }

    private function sanitizeStatus(string $value): string
    {
        return in_array($value, ['todo', 'in_progress', 'completed'], true) ? $value : 'todo';
    }

    /**
     * Safely parse a date string. Returns null on failure instead of throwing.
     */
    private function parseDate(?string $dateString): ?\DateTimeImmutable
    {
        if ($dateString === null || $dateString === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Throwable) {
            Log::warning('AiAssistantController: unparseable date from AI', ['date' => $dateString]);
            return null;
        }
    }
}
