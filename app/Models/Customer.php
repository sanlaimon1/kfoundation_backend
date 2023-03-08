<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoginLog;

class Customer extends Model
{
    use HasFactory;

    //关联
    public function level() {
        
        return $this->hasOne('App\Models\Level', 'level_id', 'level_id');
    }

    //获得最后一条登录日志, 返回ip, state, province, city, isp
    public function getLastLog() {

        $userid = $this->id;

        $ip = $state = $province = $city = $isp = '';
        $arr = ['ip'=>'', 'state'=>'', 'province'=>'', 'city'=>'', 'isp'=>'', 'created_at'=>''];

        $last_log = LoginLog::where('userid',$userid)->orderBy('created_at', 'desc')->first();
        if( !empty($last_log) ) {
            $arr['ip'] = $last_log->ip;
            $arr['state'] = $last_log->state;
            $arr['province'] = $last_log->province;
            $arr['city'] = $last_log->city;
            $arr['isp'] = $last_log->isp;
            $arr['created_at'] = $last_log->created_at;
        }

        return $arr;
    }

    //得到上级会员的手机号 
    public function getParentName() {
        $parent_id = $this->parent_id;
        if($parent_id==0) {
            return '顶级会员';
        } else {
            $one = Customer::find($parent_id);
            if(empty($one))
            {
                return '';
            }
            else
            {
                return $one->phone;
            }
        }
    }

}
