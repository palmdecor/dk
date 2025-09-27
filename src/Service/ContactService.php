<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CrudRepository;
use App\Repository\RepositoryFactory;
use App\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

final class ContactService
{
    private CrudRepository $repository;

    public function __construct(
        private RepositoryFactory $factory,
        private CsrfTokenManager $csrf,
        private array $config
    ) {
        $this->repository = $this->factory->forEntity('messages');
    }

    public function handle(Request $request): void
    {
        $token = (string) $request->request->get('_token');
        if (!$this->csrf->isTokenValid('contact_form', $token)) {
            throw new \RuntimeException('Form güvenlik doğrulaması başarısız.');
        }

        $data = [
            'name' => trim((string) $request->request->get('name')),
            'email' => trim((string) $request->request->get('email')),
            'phone' => trim((string) $request->request->get('phone')),
            'subject' => trim((string) $request->request->get('subject')),
            'message' => trim((string) $request->request->get('message')),
            'is_read' => 0,
        ];

        $this->repository->save($data);

        $dsn = $this->config['mailer_dsn'] ?? null;
        if ($dsn) {
            $mailer = new Mailer(Transport::fromDsn($dsn));
            $recipient = $this->config['contact_email'] ?: $data['email'];
            $mail = (new Email())
                ->from($data['email'])
                ->to($recipient)
                ->subject('İletişim Formu: ' . $data['subject'])
                ->text($data['message'])
                ->html('<p>' . nl2br(htmlentities($data['message'], ENT_QUOTES, 'UTF-8')) . '</p>');
            $mailer->send($mail);
        }
    }

    public function generateToken(): string
    {
        return $this->csrf->generateToken('contact_form');
    }
}
