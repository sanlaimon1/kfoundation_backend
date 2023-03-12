<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Customer;

class InvitedCodeRule implements ValidationRule
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        
        $oneuser = Customer::where( 'invited_code', $value )->where('id', '!=', $this->id)->first();
        
        if(!empty($oneuser)) {
            $fail('验证码' . $value.' 已存在.');
        }
    }

    
}
