<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\CrudRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SettingsController extends BaseController
{
    public function __construct(
        private CrudRepository $repository,
        private string $group,
        ...$dependencies
    ) {
        parent::__construct(...$dependencies);
    }

    public function form(Request $request, string $template, string $redirectUrl): Response
    {
        $this->assertPermission('settings.' . $this->group);

        if ($request->isMethod('POST')) {
            $this->assertCsrf($request);
            foreach ($request->request->all() as $key => $value) {
                if (str_starts_with($key, '_')) {
                    continue;
                }
                $this->repository->save([
                    'group_key' => $this->group,
                    'setting_key' => $key,
                    'setting_value' => is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value,
                ], $this->resolveSettingId($key));
            }

            return $this->redirect($redirectUrl);
        }

        $settings = $this->loadSettings();

        return $this->render($template, [
            'settings' => $settings,
            'token_id' => 'settings_' . $this->group,
        ]);
    }

    private function loadSettings(): array
    {
        $all = $this->repository->findAll();
        $filtered = [];
        foreach ($all as $setting) {
            if ($setting['group_key'] !== $this->group) {
                continue;
            }
            $filtered[$setting['setting_key']] = $setting;
        }

        return $filtered;
    }

    private function resolveSettingId(string $key): ?int
    {
        $stmt = $this->repository->connection()->prepare('SELECT id FROM settings WHERE group_key = :group AND setting_key = :key');
        $stmt->execute(['group' => $this->group, 'key' => $key]);
        $row = $stmt->fetch();

        return $row ? (int) $row['id'] : null;
    }
}
