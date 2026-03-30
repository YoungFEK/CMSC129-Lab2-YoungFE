<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:tasks,title,' . $this->task->id,
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date|after_or_equal:today',
            'status' => 'required|in:pending,in_progress,done',
            'priority' => 'required|in:low,medium,high',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.unique' => 'This task title already exists.',
            'due_date.after_or_equal' => 'Due date must be today or in the future.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category does not exist.',
        ];
    }
}
