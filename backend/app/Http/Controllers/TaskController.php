<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index(TaskIndexRequest $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Task::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $tasks = $query->latest()->paginate($limit);

        return response()->json([
            'message' => 'all tasks',
            'page' => $tasks->currentPage(),
            'limit' => $limit,
            'total_rows' => $tasks->total(),
            'total_page' => $tasks->lastPage(),
            'data' => $tasks->items()
        ]);
    }

    // POST /api/tasks
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending'
        ]);

        return response()->json([
            'message' => 'task created',
            'data' => $task
        ], 201);
    }

    // GET /api/tasks/{id}
    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'task not found'
            ], 404);
        }

        return response()->json([
            'message' => 'task detail',
            'data' => $task
        ]);
    }

    // PUT /api/tasks/{id}
    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'task not found'
            ], 404);
        }

        $task->update($request->only([
            'title',
            'description',
            'status' => $request->status ?? $task->status
        ]));

        return response()->json([
            'message' => 'task updated',
            'data' => $task
        ]);
    }

    // DELETE /api/tasks/{id}
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'task deleted'
        ]);
    }
}
