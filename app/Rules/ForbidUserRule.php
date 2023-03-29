<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

class ForbidUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        //$value就是用户名
        $oneuser = User::where( 'username', $value )->first();

        if(empty($oneuser)) {
           $fail("用户 {$value} 不存在.");
        }

        else if ($oneuser->status === 0) {
            $fail("用户已锁定，请联系管理员.");
        }
    }
}