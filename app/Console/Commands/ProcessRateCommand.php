<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProcessRateCommand extends Command
{
    
    protected $signature = 'process-rate';
    protected $description = '自动计算项目自增进度';

    /**
     * 自动计算项目自增进度
     */
    public function handle(): void
    {
        //查询未完成的项目进度  query projects which is not finished and increment_process>0
        $projects = Project::select(['id','project_name','fake_process','increment_process'])
            ->where('fake_process', '<', 100)
            ->where('increment_process', '>', 0)
			->where('status', 1)
			->where('enable', 1)
            ->get();
        
        //when this process runs for once,  fake_process will plus increment_process,  then update fake_process
        //if fake_process>=100,  set fake_process=100.00
        foreach($projects as $one_project) {
            $total_process = $one_project->fake_process + $one_project->increment_process;
            if($total_process >= 100) {
                $one_project->fake_process = 100.00;
                $one_project->status = 0;
            } else {
                $one_project->fake_process = $total_process;
            }
            $one_project->save();
            Log::info("项目id:" . $one_project->id . ',名称:' . $one_project->project_name . ',更新进度:' . $one_project->fake_process);
        }
		
		//查询未完成的项目进度  query projects which is not finished and increment_process>0
        $productions = Project::select(['id','production_name','fake_process','increment_process'])
            ->where('fake_process', '<', 100)
            ->where('increment_process', '>', 0)
			->where('status', 1)
            ->get();
			
		foreach($productions as $one_production) {
            $total_process = $one_production->fake_process + $one_production->increment_process;
            if($total_process >= 100) {
                $one_production->fake_process = 100.00;
				$one_project->status = 0;
            } else {
                $one_production->fake_process = $total_process;
            }
            $one_production->save();
            Log::info("商品id:" . $one_production->id . ',名称:' . $one_production->project_name . ',更新进度:' . $one_production->fake_process);
        }

    }
}
