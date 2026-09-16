<?php

namespace App\Repositories;

use App\Models\CancellationRule;
use App\Models\CommissionRule;
use App\Models\DynamicSetting;
use App\Models\RefundRule;

class SettingsRepository
{
    public function settings() { return DynamicSetting::query()->orderBy('setting_group')->orderBy('setting_key')->get(); }
    public function findSetting(string $key): ?DynamicSetting { return DynamicSetting::where('setting_key', $key)->first(); }
    public function upsertSetting(string $key, mixed $value, string $group, ?int $adminId): DynamicSetting { return DynamicSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value, 'setting_group' => $group, 'updated_by' => $adminId]); }
    public function rules(string $type) { return $this->model($type)::query()->orderBy('priority')->orderBy('id')->get(); }
    public function createRule(string $type, array $data) { return $this->model($type)::create($data); }
    public function findRule(string $type, int $id) { return $this->model($type)::findOrFail($id); }
    public function updateRule($rule, array $data) { $rule->update($data); return $rule->refresh(); }
    private function model(string $type): string { return ['commission' => CommissionRule::class, 'refund' => RefundRule::class, 'cancellation' => CancellationRule::class][$type]; }
}