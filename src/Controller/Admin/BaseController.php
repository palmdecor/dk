<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\CrudRepository;
use App\Security\Auth;
use App\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class BaseController
{
    protected Environment $twig;
    protected Auth $auth;
    protected CsrfTokenManager $csrf;

    public function __construct(Environment $twig, Auth $auth, CsrfTokenManager $csrf)
    {
        $this->twig = $twig;
        $this->auth = $auth;
        $this->csrf = $csrf;
    }

    protected function render(string $template, array $context = []): Response
    {
        $content = $this->twig->render($template, array_merge($context, [
            'currentUser' => $this->auth->user(),
            'csrf' => $this->csrf,
        ]));

        return new Response($content);
    }

    protected function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

    protected function assertPermission(string $permission): void
    {
        if (!$this->auth->checkPermission($permission)) {
            throw new \RuntimeException('Bu alana erişim yetkiniz yok.');
        }
    }

    protected function processForm(Request $request, CrudRepository $repository, array $fields, string $redirectUrl, ?int $id = null): Response
    {
        $this->assertCsrf($request);

        $data = [];
        $all = $request->request->all();
        foreach ($fields as $field) {
            $value = $all[$field] ?? $request->request->get($field);

            if (is_array($value)) {
                $data[$field] = json_encode($value, JSON_THROW_ON_ERROR);
            } else {
                $data[$field] = trim((string) $value);
            }
        }

        if (array_key_exists('password', $data)) {
            if ($data['password'] === '' && $id !== null) {
                unset($data['password']);
            } elseif ($data['password'] !== '') {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
        }

        if ($request->files->has('image') && $request->files->get('image')->isValid()) {
            $file = $request->files->get('image');
            $filename = uniqid('', true) . '.' . $file->guessExtension();
            $file->move(dirname(__DIR__, 3) . '/public/uploads', $filename);
            $data['image'] = '/uploads/' . $filename;
        }

        $repository->save($data, $id);

        return $this->redirect($redirectUrl);
    }

    protected function assertCsrf(Request $request): void
    {
        $token = $request->request->get('_token');
        $id = $request->request->get('_token_id', 'default');
        if ($token === null || !$this->csrf->isTokenValid($id, $token)) {
            throw new \RuntimeException('Form güvenlik doğrulaması başarısız oldu.');
        }
    }
}
