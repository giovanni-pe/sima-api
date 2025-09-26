<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => 'required|string|max:15|unique:users,phone',
            'email'      => 'nullable|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'user_type'  => 'required|in:passenger,driver,both',
        ];
    }

    public function messages(): array
    {
        return [
              'first_name.required' => '¡Hola! Nos encantaría saber cómo te llamas. 😊',
        'first_name.max'      => 'Tu nombre es muy largo, ¿podrías abreviarlo un poco? (máx. 100 caracteres)',

        // Apellido
        'last_name.required'  => '¡Faltó tu apellido! Así podemos dirigirnos mejor a ti. 😉',
        'last_name.max'       => 'Ese apellido es un poco extenso. Intenta que no pase de 100 caracteres.',

        // Teléfono
        'phone.required'      => '¿Cómo te contactamos? Por favor, ingresa tu número (9 dígitos, código de Perú).',
        'phone.string'        => 'Tu número debe ser solo dígitos, sin espacios ni símbolos.',
        'phone.max'           => 'Recuerda usar solo 9 dígitos para tu celular en Perú.',
        'phone.unique'        => '¡Uy! Ese número ya está registrado. ¿Quizás ya tienes una cuenta con nosotros?',

        // Email
        'email.email'         => 'Ese correo no parece válido. ¿Podrías revisarlo?',
        'email.unique'        => 'Este correo ya está en nuestra base. Si es tuyo, intenta iniciar sesión.',

        // Contraseña
        'password.required'   => 'Una contraseña te protege. ¡Elige una de al menos 6 caracteres!',
        'password.min'        => 'Casi… tu contraseña debería tener al menos 6 caracteres.',

        // Tipo de usuario
        'user_type.required'  => 'Selecciona si eres pasajero o conductor para ofrecerte la mejor experiencia.',
        'user_type.in'        => 'Hmm, esa opción no es válida. Elige “Pasajero” o “Conductor”.',

        ];
    }
}
