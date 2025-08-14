<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
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
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'service_id' => 'required|integer|exists:services,id',
            'client_email' => 'required|email',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentDate = $this->input('appointment_date');
            $appointmentTime = $this->input('appointment_time');

            if ($appointmentDate && $appointmentTime) {
                // Create appointment datetime using the application's timezone
                $appointmentDateTime = \Carbon\Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $appointmentDate . ' ' . $appointmentTime,
                    config('app.timezone')
                );

                // Get current time in the application's timezone
                $now = now();

                // Check if appointment is in the past
                if ($appointmentDateTime->lt($now)) {
                    $validator->errors()->add('appointment_time', 'Cannot book appointments for past times. Please select a future time.');
                }
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'appointment_time.date_format' => 'Please provide a valid time in HH:MM format.',
            'service_id.exists' => 'The selected service is not available.',
            'client_email.email' => 'Please provide a valid email address.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
