<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsService
{
    public function __construct(private readonly SettingsRepository $repository) {}

    public function settings(): array
    {
        $defaults = config('admin_settings', []);
        foreach ($this->repository->settings() as $setting) {
            $group = $setting->setting_group;
            $defaults[$group][$setting->setting_key] = $setting->setting_value;
        }
        return $defaults;
    }

    public function updateSettings(array $settings, Request $request): array
    {
        return DB::transaction(function () use ($settings, $request) {
            $whitelist = collect(config('admin_settings', []))->flatMap(fn ($values) => array_keys($values))->all();
            $unknown = array_values(array_diff(array_keys($settings), $whitelist));
            if ($unknown) {
                throw new \InvalidArgumentException('Unknown setting key.');
            }
            $old = [];
            foreach ($settings as $key => $value) {
                $existing = $this->repository->findSetting($key);
                $old[$key] = $existing?->setting_value ?? $this->defaultValue($key);
                $this->repository->upsertSetting($key, $value, $this->groupFor($key), $request->user()?->getKey());
            }
            $this->audit($request, 'update', $old, $settings, 0, 'DynamicSetting');
            return $this->settings();
        });
    }

    public function rules(string $type) { return $this->repository->rules($type); }
    public function createRule(string $type, array $data, Request $request)
    {
        return DB::transaction(function () use ($type, $data, $request) { $rule = $this->repository->createRule($type, $data); $this->audit($request, 'create', null, $rule->toArray(), $rule->id, class_basename($rule)); return $rule; });
    }
    public function updateRule(string $type, int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($type, $id, $data, $request) { $rule = $this->repository->findRule($type, $id); $old = $rule->toArray(); $rule = $this->repository->updateRule($rule, $data); $this->audit($request, 'update', $old, $rule->toArray(), $id, class_basename($rule)); return $rule; });
    }
    public function deleteRule(string $type, int $id, Request $request): void
    {
        DB::transaction(function () use ($type, $id, $request) { $rule = $this->repository->findRule($type, $id); $old = $rule->toArray(); $rule->delete(); $this->audit($request, 'delete', $old, null, $id, class_basename($rule)); });
    }
    private function groupFor(string $key): string { foreach (config('admin_settings', []) as $group => $values) if (array_key_exists($key, $values)) return $group; abort(422, 'Unknown setting key.'); }
    private function defaultValue(string $key): mixed { foreach (config('admin_settings', []) as $values) if (array_key_exists($key, $values)) return $values[$key]; return null; }
    private function audit(Request $request, string $action, ?array $old, ?array $new, int $id, string $type): void { DB::table('audit_logs')->insert(['uuid' => (string) Str::uuid(), 'user_id' => $request->user()?->getKey(), 'user_type' => Admin::class, 'module' => 'settings', 'action' => $action, 'entity_type' => $type, 'entity_id' => $id, 'old_data' => $old ? json_encode($old) : null, 'new_data' => $new ? json_encode($new) : null, 'ip_address' => $request->ip(), 'created_at' => now(), 'updated_at' => now()]); }
}