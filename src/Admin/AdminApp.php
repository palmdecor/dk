<?php

declare(strict_types=1);

namespace App\Admin;

use App\Controller\Admin\AuthController;
use App\Controller\Admin\CrudController;
use App\Controller\Admin\SettingsController;
use App\Kernel;
use App\Support\EntityFields;

final class AdminApp
{
    public function __construct(private Kernel $kernel)
    {
    }

    public function authController(): AuthController
    {
        return new AuthController(
            $this->kernel->twig(),
            $this->kernel->auth(),
            $this->kernel->csrf()
        );
    }

    public function crudController(string $entity, string $permissionPrefix, string $listTemplate, string $formTemplate, string $listUrl): CrudController
    {
        $fields = EntityFields::for($entity);

        return new CrudController(
            $this->kernel->repositories()->forEntity($entity),
            $permissionPrefix,
            $fields,
            $listTemplate,
            $formTemplate,
            $listUrl,
            $this->kernel->twig(),
            $this->kernel->auth(),
            $this->kernel->csrf(),
        );
    }

    public function settingsController(string $group): SettingsController
    {
        return new SettingsController(
            $this->kernel->repositories()->forEntity('settings'),
            $group,
            $this->kernel->twig(),
            $this->kernel->auth(),
            $this->kernel->csrf(),
        );
    }
}
