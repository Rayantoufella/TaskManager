<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::where('user_id' , Auth::id())->with('category')
        ->orderBy('created_at' , 'desc') 
        ->paginate(8) ;

        return view('tasks.index' , compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $categories = Category::all(); 

        return view('tasks.create' , compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'nullable|in:todo,in_progress,done',
            'due_date'    => 'nullable|date',
        ]);

        Task::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'status'      => $data['status'] ?? 'todo',
            'due_date'    => $data['due_date'] ?? null,
            'user_id'     => auth()->id(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tâche créée avec succès');
    }

    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

        $task = Task::findOrFail($id);

        if($task->user_id !== Auth::id()){
            abort(403);
        }

        return view('tasks.show' , compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $task = Task::findOrFail($id);

        if($task->user_id !== Auth::id()){
            abort(403);
        }

        $categories = Category::all();

        
        return view('tasks.edit' , compact('task' , 'categories')) ;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

            
        $data = $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'nullable|in:todo,in_progress,done',
            'due_date'    => 'nullable|date',
        ]);

        $task->update($data);

        return redirect()->route('tasks.index')->with('success', 'Tâche mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        
        $task = Task::findOrFail($id);

        if($task->user_id !== Auth::id()){
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success' , 'Task deleted successfully');
    }

    public function updateStatus(Request $request, string $id)
    {
    $task = Task::findOrFail($id);
    
    if ($task->user_id !== Auth::id()) {
        abort(403);
    }
    
    $task->update(['status' => $request->status]);

    return redirect()->route('tasks.index')->with('success', 'Statut mis à jour !');
}
}
