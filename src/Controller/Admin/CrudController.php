<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\CrudRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CrudController extends BaseController
{
    private CrudRepository $repository;
    private string $permissionPrefix;
    private array $fields;
    private string $listTemplate;
    private string $formTemplate;
    private string $listUrl;

    public function __construct(
        CrudRepository $repository,
        string $permissionPrefix,
        array $fields,
        string $listTemplate,
        string $formTemplate,
        string $listUrl,
        ...$baseDependencies
    ) {
        parent::__construct(...$baseDependencies);
        $this->repository = $repository;
        $this->permissionPrefix = $permissionPrefix;
        $this->fields = $fields;
        $this->listTemplate = $listTemplate;
        $this->formTemplate = $formTemplate;
        $this->listUrl = $listUrl;
    }

    public function index(): Response
    {
        $this->assertPermission($this->permissionPrefix . '.view');
        $items = $this->repository->findAll();

        return $this->render($this->listTemplate, ['items' => $items]);
    }

    public function create(Request $request): Response
    {
        $this->assertPermission($this->permissionPrefix . '.create');

        if ($request->isMethod('POST')) {
            return $this->processForm($request, $this->repository, $this->fields, $this->listUrl);
        }

        return $this->render($this->formTemplate, [
            'item' => null,
            'token_id' => $this->permissionPrefix . '_form',
        ]);
    }

    public function edit(int $id, Request $request): Response
    {
        $this->assertPermission($this->permissionPrefix . '.edit');
        $item = $this->repository->find($id);
        if ($item === null) {
            throw new \RuntimeException('Kayıt bulunamadı.');
        }

        if ($request->isMethod('POST')) {
            return $this->processForm($request, $this->repository, $this->fields, $this->listUrl, $id);
        }

        return $this->render($this->formTemplate, [
            'item' => $item,
            'token_id' => $this->permissionPrefix . '_form',
        ]);
    }

    public function delete(int $id, Request $request): Response
    {
        $this->assertPermission($this->permissionPrefix . '.delete');
        if ($request->isMethod('POST')) {
            $this->repository->delete($id);
        }

        return $this->redirect($this->listUrl);
    }
}
