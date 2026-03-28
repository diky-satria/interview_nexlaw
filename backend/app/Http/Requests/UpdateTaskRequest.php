<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $taskId = $this->route('id');

        return [
            'title' => 'required|string|max:255|unique:tasks,title,' . $taskId,
            'description' => 'nullable|string',
            'status' => 'nullable|in:open,pending,in_progress,testing,completed'
        ];
    }
}
