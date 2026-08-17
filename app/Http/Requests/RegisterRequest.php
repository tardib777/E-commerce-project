<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class RegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => ['required','string','min:3','max:30'],
            'lastname' => ['required','string','min:3','max:30'],
             'email' => [
                'required',
                'unique:users,email',
                Rule::email()
                    ->rfcCompliant(strict: false)
                    ->validateMxRecord()
                    ->preventSpoofing()
            ],
            'password' => ['required','confirmed',Password::min(8)
                                                        ->letters()
                                                        ->mixedCase()
                                                        ->numbers()
                                                        ->symbols()
                            ],
            'balance' => 'nullable|decimal:2'
        
                       
        ];
    }
}
