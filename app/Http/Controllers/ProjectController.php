<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = auth()->user()->projects()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->latest()
            ->get();

        $selectedProject = null;
        $tasks = collect();
        $recentTasks = collect();

        if ($request->filled('project')) {
            $selectedProject = auth()->user()->projects()->findOrFail($request->project);
            $tasks = $selectedProject->tasks()->orderBy('order')->orderBy('created_at', 'desc')->get();
            return view('dashboard', compact('projects', 'selectedProject', 'tasks'));
        } else {
            // Fetch recent tasks across all projects for the home page
            $recentTasks = auth()->user()->tasks()->with('project')->latest()->take(5)->get();
            
            // Calculate completion percentage for projects
            $projects->each(function ($project) {
                $totalTasks = $project->tasks_count;
                $completedTasks = $project->completed_tasks_count;
                $project->completion_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            });
            
            // Get all tasks for the calendar
            $allTasks = auth()->user()->tasks()->whereNotNull('due_date')->get();

            return view('home', compact('projects', 'recentTasks', 'allTasks'));
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
        ]);

        $data['icon'] = $data['icon'] ?? 'folder';
        $data['slug'] = $this->makeUniqueSlug($data['name']);

        $project = auth()->user()->projects()->create($data);

        return redirect()->route('dashboard', ['project' => $project->id])->with('status', 'Project created successfully.');
    }

    public function createProject(Request $request) {
        return $this->store($request);
    }

    public function showProject($id) {
        $project = auth()->user()->projects()->findOrFail($id);
        return view('projects.show', compact('project'));
    }

    public function updateProject(Request $request, $id) {
        $project = auth()->user()->projects()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($data);
        return redirect()->route('projects.show', $project->id);
    }

    public function settings(Project $project)
    {
        abort_unless($project->user_id === auth()->id(), 403);
        $projects = auth()->user()->projects()->withCount('tasks')->latest()->get();
        return view('projects.settings', compact('project', 'projects'));
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($project->user_id === auth()->id(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
        ]);

        $data['icon'] = $data['icon'] ?? 'folder';
        $project->update($data);

        return redirect()->route('projects.settings', $project->id)
            ->with('status', 'Project updated successfully.');
    }

    public function deleteProject($id) {
        $project = auth()->user()->projects()->findOrFail($id);
        $project->delete();
        return redirect()->route('dashboard')->with('status', 'Project deleted.');
    }

    public function findProjectBySlug($slug) {
        $project = auth()->user()->projects()->where('slug', $slug)->firstOrFail();
        return view('projects.show', compact('project'));
    
    }

    public function listProjects() {
        $projects = auth()->user()->projects()->orderBy('created_at', 'desc')->get();
        return view('projects.index', compact('projects'));
    }

    public function findProjectByName(Request $request) {
        $name = $request->input('name');
        $projects = auth()->user()->projects()->where('name', 'like', "%$name%")->get();
        return view('projects.index', compact('projects'));
    }

    public function findProjectById($id) {
        $project = auth()->user()->projects()->findOrFail($id);
        return view('projects.show', compact('project'));
    }

    public function findProjectByUserId($userId) {
        $projects = auth()->user()->projects()->where('user_id', $userId)->get();
        return view('projects.index', compact('projects'));
    }

    private function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
