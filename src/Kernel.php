<?php

declare(strict_types=1);

namespace App;

use App\Repository\RepositoryFactory;
use App\Security\Auth;
use App\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class Kernel
{
    private array $config;
    private Session $session;
    private Database $database;
    private RepositoryFactory $repositoryFactory;
    private CsrfTokenManager $csrfManager;
    private Environment $twig;
    private Auth $auth;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->session = new Session(new PhpBridgeSessionStorage());
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $this->database = new Database($this->config);
        $this->repositoryFactory = new RepositoryFactory($this->database);
        $this->csrfManager = new CsrfTokenManager();

        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => (bool) ($this->config['debug'] ?? false),
        ]);

        $this->auth = new Auth($this->session, $this->repositoryFactory->forEntity('users'));
    }

    public function config(): array
    {
        return $this->config;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function database(): Database
    {
        return $this->database;
    }

    public function repositories(): RepositoryFactory
    {
        return $this->repositoryFactory;
    }

    public function csrf(): CsrfTokenManager
    {
        return $this->csrfManager;
    }

    public function twig(): Environment
    {
        return $this->twig;
    }

    public function auth(): Auth
    {
        return $this->auth;
    }
}
