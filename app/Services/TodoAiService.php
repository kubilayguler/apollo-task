<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use Laravel\Ai\AnonymousAgent;
use RuntimeException;

/**
 * Service responsible for interacting with the Gemini LLM via the Laravel AI SDK
 * to interpret natural-language commands and contextual actions on tasks.
 */
final class TodoAiService
{
    /** The Gemini provider name (must match config/ai.php). */
    private const string PROVIDER = 'gemini';

    /** Allowed AI action types that the controller knows how to execute. */
    private const array VALID_ACTIONS = [
        'create_tasks',
        'split_existing_task',
        'auto_schedule',
        'optimize_description',
        'update_task',
    ];

    /** Valid priority values matching the DB enum. */
    private const array VALID_PRIORITIES = ['none', 'low', 'medium', 'high'];

    /** Valid status values matching the DB enum. */
    private const array VALID_STATUSES = ['todo', 'in_progress', 'completed'];

    // ──────────────────────────────────────────────────────────────
    //  System Prompt
    // ──────────────────────────────────────────────────────────────

    /**
     * Build the system prompt that constrains Gemini's output to strict JSON.
     */
    private function buildSystemPrompt(): string
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i');

        return <<<PROMPT
        You are a task-management AI assistant embedded inside a To-Do application.
        Today's date is {$today} and the current time is {$currentTime}.

        ── RULES ──────────────────────────────────────────────────────
        1. You MUST respond with **valid JSON only** — no markdown fences, no commentary.
        2. The root JSON object MUST contain exactly three keys:
           • "action"           – one of: create_tasks, split_existing_task, auto_schedule, optimize_description, update_task
           • "parameters"       – object whose shape depends on the action (see below)
           • "feedback_message" – a short, friendly success message for the user (≤ 120 chars)

        ── ACTION SCHEMAS ─────────────────────────────────────────────

        ### create_tasks
        Create one or more new tasks (optionally with sub-tasks).
        "parameters": {
          "project_name": "string – a short project name when the user implies one (use the command context or default to 'General')",
          "tasks": [
            {
              "title": "string",
              "content": "string | null – optional description",
              "priority": "none | low | medium | high",
              "status": "todo",
              "due_date": "YYYY-MM-DD HH:mm | null",
              "subtasks": [
                {
                  "title": "string",
                  "content": "string | null",
                  "priority": "none | low | medium | high",
                  "status": "todo",
                  "due_date": "YYYY-MM-DD HH:mm | null"
                }
              ]
            }
          ]
        }

        ### split_existing_task
        Break an existing task into sub-tasks.
        "parameters": {
          "updated_title": "string | null – optionally improve the parent task title",
          "updated_content": "string | null – optionally improve the parent task description",
          "subtasks": [
            {
              "title": "string",
              "content": "string | null",
              "priority": "none | low | medium | high",
              "status": "todo",
              "due_date": "YYYY-MM-DD HH:mm | null"
            }
          ]
        }

        ### auto_schedule
        Suggest/update due dates for a task and optionally its sub-tasks.
        "parameters": {
          "due_date": "YYYY-MM-DD HH:mm",
          "subtasks": [
            {
              "title": "string",
              "due_date": "YYYY-MM-DD HH:mm"
            }
          ]
        }

        ### optimize_description
        Rewrite or enhance a task's title/content.
        "parameters": {
          "updated_title": "string",
          "updated_content": "string"
        }

        ### update_task
        General-purpose update of any task fields.
        "parameters": {
          "title": "string | null",
          "content": "string | null",
          "priority": "none | low | medium | high | null",
          "status": "todo | in_progress | completed | null",
          "due_date": "YYYY-MM-DD HH:mm | null"
        }

        ── CONSTRAINTS ────────────────────────────────────────────────
        • priority MUST be one of: none, low, medium, high
        • status MUST be one of: todo, in_progress, completed
        • Dates MUST use the format YYYY-MM-DD HH:mm (24-hour). If the user says "Saturday" without a time, default to 09:00.
        • When the user asks to "plan", "break down", or "split" a task, use split_existing_task.
        • When the user asks to "schedule" or "reschedule", use auto_schedule.
        • When the user asks to "improve", "rewrite", or "optimize" a description, use optimize_description.
        • Never include any text outside the JSON object.
        PROMPT;
    }

    // ──────────────────────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────────────────────

    /**
     * Process a free-text command from the user (Text-to-Action).
     *
     * @param  string  $command  The natural-language instruction.
     * @return array{action: string, parameters: array<string, mixed>, feedback_message: string}
     *
     * @throws RuntimeException  When the AI call fails or returns unparseable output.
     */
    public function processTextCommand(string $command): array
    {
        $userMessage = "User command: \"{$command}\"";

        return $this->callAi($userMessage);
    }

    /**
     * Process a contextual action on an existing task (Button Click).
     *
     * @param  Task    $task           The task entity to act upon.
     * @param  string  $actionHint     UI-supplied hint: "break_down" | "auto_schedule" | "optimize" | "general"
     * @param  string  $extraContext   Optional additional user instructions.
     * @return array{action: string, parameters: array<string, mixed>, feedback_message: string}
     *
     * @throws RuntimeException
     */
    public function processContextualAction(Task $task, string $actionHint = 'general', string $extraContext = ''): array
    {
        $taskPayload = [
            'id' => $task->id,
            'title' => $task->title,
            'content' => $task->content,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->format('Y-m-d H:i'),
            'existing_subtasks' => $task->subtasks->map(fn (Task $st) => [
                'id' => $st->id,
                'title' => $st->title,
                'status' => $st->status,
                'due_date' => $st->due_date?->format('Y-m-d H:i'),
            ])->toArray(),
        ];

        $taskJson = json_encode($taskPayload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $hintMap = [
            'break_down' => 'The user wants you to split/break down this task into actionable sub-tasks.',
            'auto_schedule' => 'The user wants you to auto-schedule this task (and any sub-tasks) with sensible due dates.',
            'optimize' => 'The user wants you to optimize/improve the title and description of this task.',
            'general' => 'Analyse the task and decide the best action to take.',
        ];

        $instruction = $hintMap[$actionHint] ?? $hintMap['general'];

        $userMessage = <<<MSG
        {$instruction}

        Additional user instructions: "{$extraContext}"

        Current task data:
        {$taskJson}
        MSG;

        return $this->callAi($userMessage);
    }

    // ──────────────────────────────────────────────────────────────
    //  Internals
    // ──────────────────────────────────────────────────────────────

    /**
     * Create an AnonymousAgent with the system prompt and call the Gemini provider.
     *
     * @throws RuntimeException
     */
    private function callAi(string $userMessage): array
    {
        $rawText = '';

        try {
            $agent = new AnonymousAgent(
                instructions: $this->buildSystemPrompt(),
                messages: [],
                tools: [],
            );

            $response = $agent->prompt(
                prompt: $userMessage,
                provider: self::PROVIDER,
            );

            $rawText = trim($response->text);

            return $this->parseResponse($rawText);
        } catch (JsonException $e) {
            Log::error('TodoAiService: JSON parse failure', [
                'raw' => $rawText,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('The AI returned an invalid response. Please try again.', previous: $e);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('TodoAiService: AI SDK error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new RuntimeException('Failed to communicate with the AI service. Please try again later.', previous: $e);
        }
    }

    /**
     * Parse and validate the raw JSON string returned by the model.
     *
     * @throws JsonException|InvalidArgumentException
     */
    private function parseResponse(string $raw): array
    {
        // Strip markdown code fences if the model wraps output despite instructions.
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $raw);
        $cleaned = trim((string) $cleaned);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($cleaned, true, 64, JSON_THROW_ON_ERROR);

        // Structural validation
        if (! isset($decoded['action'], $decoded['parameters'], $decoded['feedback_message'])) {
            throw new InvalidArgumentException(
                'AI response missing required keys: action, parameters, feedback_message.'
            );
        }

        if (! in_array($decoded['action'], self::VALID_ACTIONS, true)) {
            throw new InvalidArgumentException(
                "Unknown AI action: {$decoded['action']}"
            );
        }

        return [
            'action' => $decoded['action'],
            'parameters' => (array) $decoded['parameters'],
            'feedback_message' => (string) $decoded['feedback_message'],
        ];
    }
}
