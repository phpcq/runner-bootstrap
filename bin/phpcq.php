<?php

declare(strict_types=1);

exit(
    (new class() {
        // TODO: Correct path?
        private const DOWNLOAD_URL = 'https://phpcq.github.io/phpcq/phpcq-latest.phar';

        /** @var string */
        private $pharPath;

        /** @var string */
        private $phpBinary;

        public function __construct()
        {
            $this->pharPath  = __DIR__ . DIRECTORY_SEPARATOR . 'phpcq.phar';
            $this->phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        }

        public function __invoke(array $argv): int
        {
            if (!file_exists($this->pharPath)) {
                $this->downloadPhar();
            }

            $command = $this->buildCommand($argv);
            passthru($command, $status);

            return $status;
        }

        private function downloadPhar(): void
        {
            // TODO: We might have a repository.json containing information about the versions and the php dependencies
            // We might check the constraints, defined in the composer.json or maybe our phpcq.yml.dist file
            echo 'Downloading phpcq.phar from ' . self::DOWNLOAD_URL . PHP_EOL;
            file_put_contents($this->pharPath, file_get_contents(self::DOWNLOAD_URL));

            if (!file_exists($this->pharPath)) {
                throw new RuntimeException('Downloading phar failed');
            }

            // TODO: We should have a signature and validate it here. We need a place to define the trusted fingerprints
            // If we do signing, we probably have to require phpcq/gnupg

            chmod($this->pharPath, 0755);
            if (!is_executable($this->pharPath)) {
                throw new RuntimeException('Downloaded phar is not executable');
            }
        }

        private function buildCommand(array $arguments): string
        {
            // Drop current command
            array_shift($arguments);

            // Append php binary and the path
            array_unshift($arguments, $this->phpBinary, $this->pharPath);

            // TODO: Should we force --ansi by default?

            return implode(' ', array_map('escapeshellarg', $arguments));
        }
    })($argv ?? [])
);
