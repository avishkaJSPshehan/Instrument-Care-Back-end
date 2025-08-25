<?php
namespace App\Core;

final class Request
{
    public function __construct(
        private string $method,
        private string $path,
        private array $query,
        private array $post,
        private string $rawBody
    ) {}

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(): array { return $this->query; }

    public function json(): array
    {
        $data = json_decode($this->rawBody, true);
        return is_array($data) ? $data : [];
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $json = $this->json();
        return $json[$key] ?? $this->post[$key] ?? $this->query[$key] ?? $default;
    }
}
