<?php

namespace App\Http\Requests\Client;

use App\Models\ClientIdentityVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class SubmitClientIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('submit', ClientIdentityVerification::class);
    }

    public function rules(): array
    {
        return [
            'id_type'      => ['required', 'string', 'in:philsys,passport,drivers_license,umid,other'],
            'id'           => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:' . config('kaayos.client_identity.max_id_kb', 5120),
            ],
            'selfie'       => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:' . config('kaayos.client_identity.max_selfie_kb', 5120),
                'dimensions:min_width=400,min_height=400',
            ],
            'consent'      => ['required', 'accepted'],
            'age_attested' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'      => 'Please upload a government ID.',
            'id.mimes'         => 'The ID must be a JPG, PNG, or PDF file.',
            'id.max'           => 'The ID file is too large (maximum :max KB).',
            'selfie.required'  => 'Please upload a selfie.',
            'selfie.mimes'     => 'The selfie must be a JPG, PNG, or WEBP file.',
            'selfie.max'       => 'The selfie file is too large (maximum :max KB).',
            'selfie.dimensions'=> 'The selfie must be at least 400x400 pixels.',
            'consent.accepted' => 'You must consent to identity verification.',
            'age_attested.accepted' => 'You must attest that you are 18 years or older.',
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach (['id', 'selfie'] as $field) {
                if ($this->hasFile($field) && ! $this->file($field)->isValid()) {
                    $validator->errors()->add($field, 'The uploaded file could not be processed. Please try again.');
                }
            }
        });
    }
}
