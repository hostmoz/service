<?php

namespace SpondonIt\Service\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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

        $rules = array();

        $rules = [
            'name'                  => 'required|max:191',
            'email'                 => 'required|email',
            'username'              => 'sometimes|nullable|string',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ];

        return $rules;
    }

    /**
     * Translate fields with user friendly name.
     *
     * @return array
     */
    public function attributes()
    {
        return [

            'name'                  => trans('service::install.name'),
            'email'                 => trans('service::install.email'),
            'username'              => trans('service::install.username'),
            'contact_number'        => trans('service::install.contact_number'),
            'password'              => trans('service::install.password'),
            'password_confirmation' => trans('service::install.password_confirmation'),
        ];
    }
}
