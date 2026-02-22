<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($project->user_id === auth()->id(), 403);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'priority' => 'nullable|in:none,low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $data['priority'] = $data['priority'] ?? 'none';

        $project->tasks()->create($data);

        return redirect()->route('dashboard', ['project' => $project->id]);
    }

    public function toggle(Task $task)
    {
        abort_unless($task->project->user_id === auth()->id(), 403);

        $newStatus = $task->status === 'completed' ? 'todo' : 'completed';
        $completedAt = $newStatus === 'completed' ? now() : null;

        $task->update([
            'status'       => $newStatus,
            'completed_at' => $completedAt,
        ]);

        // When a parent task is completed, also complete all subtasks
        if ($newStatus === 'completed') {
            $task->subtasks()
                ->where('status', '!=', 'completed')
                ->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
        }

        // When a parent task is unchecked, also uncheck all subtasks
        if ($newStatus === 'todo') {
            $task->subtasks()
                ->where('status', 'completed')
                ->update([
                    'status'       => 'todo',
                    'completed_at' => null,
                ]);
        }

        return back();
    }

    public function destroy(Task $task)
    {
        abort_unless($task->project->user_id === auth()->id(), 403);
        $projectId = $task->project_id;
        $task->delete();
        return redirect()->route('dashboard', ['project' => $projectId]);
    }

    public function createTask(Request $request) {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'priority' => 'nullable|integer',
            'status' => 'nullable|string',
            'due_date' => 'nullable|date',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        auth()->user()->projects()->findOrFail($data['project_id'])->tasks()->create($data);

        return redirect()->route('projects.show', $data['project_id']);
    }

    public function updateTask(Request $request, $id) {
        $task = auth()->user()->projects()->findOrFail($request->project_id)->tasks()->findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'priority' => 'nullable|integer',
            'status' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $task->update($data);
        return redirect()->route('projects.show', $task->project_id);
    }

    public function deleteTask(Request $request, $id) {
        $task = auth()->user()->projects()->findOrFail($request->project_id)->tasks()->findOrFail($id);
        $task->delete();
        return redirect()->route('projects.show', $task->project_id);
    }

    public function toggleComplete(Request $request, $id) {
        $task = auth()->user()->projects()->findOrFail($request->project_id)->tasks()->findOrFail($id);
        $task->update(['status' => $task->status === 'completed' ? 'pending' : 'completed']);
        return redirect()->route('projects.show', $task->project_id);
    }

    public function reorderTasks(Request $request, $projectId) {
        $project = auth()->user()->projects()->findOrFail($projectId);
        $taskIds = $request->input('task_ids');

        foreach ($taskIds as $index => $taskId) {
            $project->tasks()->where('id', $taskId)->update(['order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }

    public function findTaskById($id) {
        $task = auth()->user()->projects()->findOrFail(request()->project_id)->tasks()->findOrFail($id);
        return response()->json($task);
    }

    public function listTasks($projectId) {
        $project = auth()->user()->projects()->findOrFail($projectId);
        $tasks = $project->tasks()->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }

    public function findTasksByStatus(Request $request, $projectId) {
        $status = $request->input('status');
        $project = auth()->user()->projects()->findOrFail($projectId);
        $tasks = $project->tasks()->where('status', $status)->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }

    public function findTasksByDueDate(Request $request, $projectId) {
        $dueDate = $request->input('due_date');
        $project = auth()->user()->projects()->findOrFail($projectId);
        $tasks = $project->tasks()->whereDate('due_date', $dueDate)->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }

    public function findTaskByName(Request $request, $projectId) {
        $name = $request->input('name');
        $project = auth()->user()->projects()->findOrFail($projectId);
        $tasks = $project->tasks()->where('title', 'like', "%$name%")->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);
    }
}
