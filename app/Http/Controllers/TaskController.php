<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tasks = $user->tasks;

        if (count($tasks) > 0) {
            return ApiResponse::sendResponse(200, 'Tasks Retrieved', TaskResource::collection($tasks));
        }
        return ApiResponse::sendResponse(200, 'No tasks found', []);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'unique:tasks,name'],
            ],
            [],
            [
                'name' => 'Name'
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }

        $task = Task::where('name', $request->name)->get();

        $data = [
            'name' => $request->name,
            'user_id' => $user->id,
        ];
        $task = Task::create($data);
        return ApiResponse::sendResponse(201, 'Task created', new TaskResource($task));
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'exists:tasks,name'],
                'newName' => ['required'],
            ],
            [],
            [
                'name' => 'Name',
                'newName' => 'New Name',
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }
        $task = Task::where('name', $request->name)->first();
        $task->update(['name' => $request->newName]);
        return ApiResponse::sendResponse(200, 'Task Updated', new TaskResource($task));
    }

    public function status(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'exists:tasks,name'],
            ],
            [],
            [
                'name' => 'Name',
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }
        $task = Task::where('name', $request->name)->first();
        if($task->status == 'active'){
            $task->status = 'done';
            $task->save();
        }
        else{
            $task->status = 'active';
            $task->save();
        }
        return ApiResponse::sendResponse(200, 'Task Updated', new TaskResource($task));
    }


    public function delete(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'exists:tasks,name'],
            ],
            [],
            [
                'name' => 'Name'
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation Error', $validator->errors()->messages());
        }
        $task = Task::where('name', $request->name)->first();
        $task->delete();
        return ApiResponse::sendResponse(200, 'Task deleted', []);
    }
}
