<?php
namespace App\Domain\Operations; use App\Models\AuditLog; use Illuminate\Http\Request;
class Audit {public function write(string $action,?object $subject=null,array $context=[],?Request $r=null):AuditLog{return AuditLog::create(['user_id'=>auth()->id(),'action'=>$action,'subject_type'=>$subject?get_class($subject):null,'subject_id'=>$subject->id??null,'ip'=>$r?->ip(),'context'=>$context]);}}
